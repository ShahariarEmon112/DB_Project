<?php
/**
 * GameArena - Tournaments API
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            $db = getDB();
            $action = $_GET['action'] ?? 'list';

            if ($action === 'list') {
                $sql = "SELECT t.*, gc.category_name,
                            (SELECT COUNT(*) FROM tournament_registrations tr WHERE tr.tournament_id = t.tournament_id AND tr.status = 'Confirmed') AS registered_teams
                        FROM tournaments t
                        JOIN game_categories gc ON t.category_id = gc.category_id
                        WHERE 1=1";
                $params = [];

                if (!empty($_GET['status'])) {
                    $sql .= " AND t.status = :status";
                    $params[':status'] = $_GET['status'];
                }
                if (!empty($_GET['category'])) {
                    $sql .= " AND t.category_id = :category";
                    $params[':category'] = $_GET['category'];
                }
                if (!empty($_GET['search'])) {
                    $sql .= " AND UPPER(t.tournament_name) LIKE :search";
                    $params[':search'] = '%' . strtoupper($_GET['search']) . '%';
                }

                $sql .= " ORDER BY t.start_date DESC";
                $tournaments = $db->fetchAll($sql, $params);
                jsonResponse(['success' => true, 'data' => $tournaments]);
            }

            if ($action === 'get' && isset($_GET['id'])) {
                $id = (int)$_GET['id'];
                $tournament = $db->fetchOne(
                    "SELECT t.*, gc.category_name,
                            (SELECT COUNT(*) FROM tournament_registrations tr WHERE tr.tournament_id = t.tournament_id AND tr.status = 'Confirmed') AS registered_teams
                     FROM tournaments t
                     JOIN game_categories gc ON t.category_id = gc.category_id
                     WHERE t.tournament_id = :id",
                    [':id' => $id]
                );
                jsonResponse(['success' => true, 'data' => $tournament]);
            }

            if ($action === 'leaderboard' && isset($_GET['id'])) {
                $id = (int)$_GET['id'];
                $leaderboard = $db->fetchAll(
                    "SELECT lb.*, t.team_name, t.department
                     FROM leaderboard lb
                     JOIN teams t ON lb.team_id = t.team_id
                     WHERE lb.tournament_id = :id
                     ORDER BY lb.rank_position ASC",
                    [':id' => $id]
                );
                jsonResponse(['success' => true, 'data' => $leaderboard]);
            }

            jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            Auth::requireAdmin();
            $db = getDB();

            $sql = "INSERT INTO tournaments (tournament_name, category_id, description, start_date, end_date, registration_deadline, max_teams, min_teams, prize_pool, entry_fee, status, venue, created_by)
                    VALUES (:name, :category, :description, :start, :end, :deadline, :max, :min, :prize, :fee, :status, :venue, :created_by)";

            $db->insert($sql, [
                ':name' => $data['tournament_name'],
                ':category' => $data['category_id'],
                ':description' => $data['description'] ?? '',
                ':start' => $data['start_date'],
                ':end' => $data['end_date'] ?? null,
                ':deadline' => $data['registration_deadline'],
                ':max' => $data['max_teams'] ?? 16,
                ':min' => $data['min_teams'] ?? 4,
                ':prize' => $data['prize_pool'] ?? 0,
                ':fee' => $data['entry_fee'] ?? 0,
                ':status' => $data['status'] ?? 'Upcoming',
                ':venue' => $data['venue'] ?? '',
                ':created_by' => Auth::getUserId()
            ]);

            jsonResponse(['success' => true, 'message' => 'Tournament created'], 201);
            break;

        default:
            jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    }
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
}
