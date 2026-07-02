<?php
/**
 * GameArena - Matches API
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
                $sql = "SELECT m.*, t1.team_name AS team1_name, t2.team_name AS team2_name,
                            trn.tournament_name, gc.category_name,
                            mr.team1_score, mr.team2_score,
                            tw.team_name AS winner_name
                        FROM matches m
                        JOIN teams t1 ON m.team1_id = t1.team_id
                        JOIN teams t2 ON m.team2_id = t2.team_id
                        JOIN tournaments trn ON m.tournament_id = trn.tournament_id
                        JOIN game_categories gc ON trn.category_id = gc.category_id
                        LEFT JOIN match_results mr ON m.match_id = mr.match_id
                        LEFT JOIN teams tw ON mr.winner_id = tw.team_id
                        WHERE 1=1";
                $params = [];

                if (!empty($_GET['status'])) {
                    $sql .= " AND m.status = :status";
                    $params[':status'] = $_GET['status'];
                }
                if (!empty($_GET['tournament'])) {
                    $sql .= " AND m.tournament_id = :tournament";
                    $params[':tournament'] = $_GET['tournament'];
                }
                if (!empty($_GET['team'])) {
                    $sql .= " AND (m.team1_id = :team OR m.team2_id = :team)";
                    $params[':team'] = $_GET['team'];
                }

                $sql .= " ORDER BY m.match_date DESC";
                $matches = $db->fetchAll($sql, $params);
                jsonResponse(['success' => true, 'data' => $matches]);
            }

            if ($action === 'upcoming') {
                $limit = (int)($_GET['limit'] ?? 10);
                $matches = $db->fetchAll(
                    "SELECT m.*, t1.team_name AS team1_name, t2.team_name AS team2_name,
                            trn.tournament_name
                     FROM matches m
                     JOIN teams t1 ON m.team1_id = t1.team_id
                     JOIN teams t2 ON m.team2_id = t2.team_id
                     JOIN tournaments trn ON m.tournament_id = trn.tournament_id
                     WHERE m.status IN ('Scheduled', 'Live')
                     ORDER BY m.match_date ASC
                     FETCH FIRST :limit ROWS ONLY",
                    [':limit' => $limit]
                );
                jsonResponse(['success' => true, 'data' => $matches]);
            }

            if ($action === 'get' && isset($_GET['id'])) {
                $id = (int)$_GET['id'];
                $match = $db->fetchOne(
                    "SELECT m.*, t1.team_name AS team1_name, t2.team_name AS team2_name,
                            trn.tournament_name,
                            mr.team1_score, mr.team2_score, mr.winner_id,
                            tw.team_name AS winner_name,
                            mv.full_name AS mvp_name
                     FROM matches m
                     JOIN teams t1 ON m.team1_id = t1.team_id
                     JOIN teams t2 ON m.team2_id = t2.team_id
                     JOIN tournaments trn ON m.tournament_id = trn.tournament_id
                     LEFT JOIN match_results mr ON m.match_id = mr.match_id
                     LEFT JOIN teams tw ON mr.winner_id = tw.team_id
                     LEFT JOIN users mv ON mr.mvp_player_id = mv.user_id
                     WHERE m.match_id = :id",
                    [':id' => $id]
                );
                jsonResponse(['success' => true, 'data' => $match]);
            }

            jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            Auth::requireAdmin();
            $db = getDB();

            $action = $data['action'] ?? '';

            if ($action === 'create') {
                $newId = (int)$db->fetchColumn("SELECT NVL(MAX(match_id), 0) + 1 FROM matches");
                $sql = "INSERT INTO matches (match_id, tournament_id, match_name, team1_id, team2_id, match_date, match_time, venue, round, status)
                        VALUES (:match_id, :tournament, :name, :team1, :team2, :date, :time, :venue, :round, 'Scheduled')";

                $db->insert($sql, [
                    ':match_id' => $newId,
                    ':tournament' => $data['tournament_id'],
                    ':name' => $data['match_name'],
                    ':team1' => $data['team1_id'],
                    ':team2' => $data['team2_id'],
                    ':date' => $data['match_date'],
                    ':time' => $data['match_time'] ?? '',
                    ':venue' => $data['venue'] ?? '',
                    ':round' => $data['round'] ?? 'Group Stage'
                ]);

                jsonResponse(['success' => true, 'message' => 'Match created'], 201);
            }

            if ($action === 'update_result') {
                $newResultId = (int)$db->fetchColumn("SELECT NVL(MAX(result_id), 0) + 1 FROM match_results");
                $db->insert(
                    "INSERT INTO match_results (result_id, match_id, team1_score, team2_score, winner_id, mvp_player_id, duration_mins, notes, recorded_by)
                     VALUES (:result_id, :match, :score1, :score2, :winner, :mvp, :duration, :notes, :recorded_by)",
                    [
                        ':result_id' => $newResultId,
                        ':match' => $data['match_id'],
                        ':score1' => $data['team1_score'] ?? 0,
                        ':score2' => $data['team2_score'] ?? 0,
                        ':winner' => $data['winner_id'],
                        ':mvp' => $data['mvp_player_id'] ?? null,
                        ':duration' => $data['duration_mins'] ?? null,
                        ':notes' => $data['notes'] ?? '',
                        ':recorded_by' => Auth::getUserId()
                    ]
                );

                $db->update(
                    "UPDATE matches SET status = 'Completed' WHERE match_id = :id",
                    [':id' => $data['match_id']]
                );

                $db->commit();
                jsonResponse(['success' => true, 'message' => 'Result recorded']);
            }

            jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
            break;

        default:
            jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    }
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
}
