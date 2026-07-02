<?php
/**
 * GameArena - Teams API
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
                $sql = "SELECT t.*, u.full_name AS captain_name,
                            (SELECT COUNT(*) FROM team_members tm WHERE tm.team_id = t.team_id) AS member_count,
                            NVL(GET_TEAM_WIN_RATE(t.team_id), 0) AS win_rate
                        FROM teams t
                        LEFT JOIN users u ON t.captain_id = u.user_id
                        WHERE t.is_active = 1";
                $params = [];

                if (!empty($_GET['department'])) {
                    $sql .= " AND t.department = :dept";
                    $params[':dept'] = $_GET['department'];
                }
                if (!empty($_GET['search'])) {
                    $sql .= " AND UPPER(t.team_name) LIKE :search";
                    $params[':search'] = '%' . strtoupper($_GET['search']) . '%';
                }

                $sql .= " ORDER BY t.team_name";
                $teams = $db->fetchAll($sql, $params);
                jsonResponse(['success' => true, 'data' => $teams]);
            }

            if ($action === 'get' && isset($_GET['id'])) {
                $id = (int)$_GET['id'];
                $team = $db->fetchOne(
                    "SELECT t.*, u.full_name AS captain_name
                     FROM teams t
                     LEFT JOIN users u ON t.captain_id = u.user_id
                     WHERE t.team_id = :id",
                    [':id' => $id]
                );

                $members = $db->fetchAll(
                    "SELECT tm.*, u.username, u.full_name, u.email
                     FROM team_members tm
                     JOIN users u ON tm.user_id = u.user_id
                     WHERE tm.team_id = :id",
                    [':id' => $id]
                );

                $team['members'] = $members;
                jsonResponse(['success' => true, 'data' => $team]);
            }

            jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            Auth::requireLogin();
            $db = getDB();

            $newTeamId = (int)$db->fetchColumn("SELECT NVL(MAX(team_id), 0) + 1 FROM teams");
            $sql = "INSERT INTO teams (team_id, team_name, description, department, captain_id)
                    VALUES (:team_id, :name, :desc, :dept, :captain)";

            $db->insert($sql, [
                ':team_id' => $newTeamId,
                ':name' => $data['team_name'],
                ':desc' => $data['description'] ?? '',
                ':dept' => $data['department'] ?? '',
                ':captain' => Auth::getUserId()
            ]);

            $newMemberId = (int)$db->fetchColumn("SELECT NVL(MAX(member_id), 0) + 1 FROM team_members");
            $db->insert(
                "INSERT INTO team_members (member_id, team_id, user_id, role_in_team) VALUES (:mid, :team, :user, 'Captain')",
                [':mid' => $newMemberId, ':team' => $newTeamId, ':user' => Auth::getUserId()]
            );

            jsonResponse(['success' => true, 'message' => 'Team created'], 201);
            break;

        default:
            jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    }
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
}
