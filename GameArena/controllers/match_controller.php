<?php
/**
 * GameArena - Match Controller
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../models/MatchModel.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            $filters = [
                'status' => $_GET['status'] ?? '',
                'tournament' => $_GET['tournament'] ?? '',
                'team' => $_GET['team'] ?? '',
                'search' => $_GET['search'] ?? '',
                'sort' => $_GET['sort'] ?? 'match_date',
                'direction' => $_GET['direction'] ?? 'DESC'
            ];
            $matches = MatchModel::getAll($filters);
            jsonResponse(['success' => true, 'data' => $matches]);
            break;

        case 'upcoming':
            $limit = (int)($_GET['limit'] ?? 10);
            $matches = MatchModel::getUpcoming($limit);
            jsonResponse(['success' => true, 'data' => $matches]);
            break;

        case 'get':
            $id = (int)($_GET['id'] ?? 0);
            $match = MatchModel::getById($id);
            if ($match) {
                jsonResponse(['success' => true, 'data' => $match]);
            } else {
                jsonResponse(['success' => false, 'message' => 'Match not found'], 404);
            }
            break;

        case 'create':
            Auth::requireAdmin();
            $data = [
                'tournament_id' => (int)$_POST['tournament_id'],
                'match_name' => sanitize($_POST['match_name']),
                'team1_id' => (int)$_POST['team1_id'],
                'team2_id' => (int)$_POST['team2_id'],
                'match_date' => $_POST['match_date'],
                'match_time' => sanitize($_POST['match_time']),
                'venue' => sanitize($_POST['venue']),
                'round' => sanitize($_POST['round'])
            ];
            $id = MatchModel::create($data);
            jsonResponse(['success' => true, 'message' => 'Match created', 'id' => $id]);
            break;

        case 'update':
            Auth::requireAdmin();
            $id = (int)$_POST['match_id'];
            $data = [
                'match_name' => sanitize($_POST['match_name']),
                'tournament_id' => (int)$_POST['tournament_id'],
                'team1_id' => (int)$_POST['team1_id'],
                'team2_id' => (int)$_POST['team2_id'],
                'match_date' => $_POST['match_date'],
                'match_time' => sanitize($_POST['match_time']),
                'venue' => sanitize($_POST['venue']),
                'round' => sanitize($_POST['round']),
                'status' => $_POST['status'] ?? 'Scheduled'
            ];
            MatchModel::update($id, $data);
            jsonResponse(['success' => true, 'message' => 'Match updated']);
            break;

        case 'update_result':
            Auth::requireAdmin();
            $matchId = (int)$_POST['match_id'];
            $data = [
                'team1_score' => (int)$_POST['team1_score'],
                'team2_score' => (int)$_POST['team2_score'],
                'winner_id' => (int)$_POST['winner_id'],
                'mvp_player_id' => !empty($_POST['mvp_player_id']) ? (int)$_POST['mvp_player_id'] : null,
                'duration_mins' => !empty($_POST['duration_mins']) ? (int)$_POST['duration_mins'] : null,
                'notes' => sanitize($_POST['notes'] ?? ''),
                'recorded_by' => Auth::getUserId()
            ];
            MatchModel::updateResult($matchId, $data);
            jsonResponse(['success' => true, 'message' => 'Match result updated']);
            break;

        case 'delete':
            Auth::requireAdmin();
            $id = (int)($_GET['id'] ?? 0);
            MatchModel::delete($id);
            jsonResponse(['success' => true, 'message' => 'Match deleted']);
            break;

        case 'stats':
            Auth::requireAdmin();
            $stats = MatchModel::getStats();
            jsonResponse(['success' => true, 'data' => $stats]);
            break;

        default:
            jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
    }
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
}
