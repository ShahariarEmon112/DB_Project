<?php
/**
 * GameArena - Tournament Controller
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../models/TournamentModel.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            $filters = [
                'status' => $_GET['status'] ?? '',
                'category' => $_GET['category'] ?? '',
                'search' => $_GET['search'] ?? '',
                'sort' => $_GET['sort'] ?? 'start_date',
                'direction' => $_GET['direction'] ?? 'DESC'
            ];
            $tournaments = TournamentModel::getAll($filters);
            jsonResponse(['success' => true, 'data' => $tournaments]);
            break;

        case 'get':
            $id = (int)($_GET['id'] ?? 0);
            $tournament = TournamentModel::getWithStats($id);
            if ($tournament) {
                jsonResponse(['success' => true, 'data' => $tournament]);
            } else {
                jsonResponse(['success' => false, 'message' => 'Tournament not found'], 404);
            }
            break;

        case 'create':
            Auth::requireAdmin();
            $data = [
                'tournament_name' => sanitize($_POST['tournament_name']),
                'category_id' => (int)$_POST['category_id'],
                'description' => sanitize($_POST['description']),
                'start_date' => $_POST['start_date'],
                'end_date' => $_POST['end_date'] ?: null,
                'registration_deadline' => $_POST['registration_deadline'],
                'max_teams' => (int)$_POST['max_teams'],
                'min_teams' => (int)$_POST['min_teams'],
                'prize_pool' => (float)$_POST['prize_pool'],
                'entry_fee' => (float)$_POST['entry_fee'],
                'status' => $_POST['status'] ?? 'Upcoming',
                'venue' => sanitize($_POST['venue']),
                'rules' => sanitize($_POST['rules']),
                'created_by' => Auth::getUserId()
            ];
            $id = TournamentModel::create($data);
            jsonResponse(['success' => true, 'message' => 'Tournament created', 'id' => $id]);
            break;

        case 'update':
            Auth::requireAdmin();
            $id = (int)$_POST['tournament_id'];
            $data = [
                'tournament_name' => sanitize($_POST['tournament_name']),
                'category_id' => (int)$_POST['category_id'],
                'description' => sanitize($_POST['description']),
                'start_date' => $_POST['start_date'],
                'end_date' => $_POST['end_date'] ?: null,
                'registration_deadline' => $_POST['registration_deadline'],
                'max_teams' => (int)$_POST['max_teams'],
                'min_teams' => (int)$_POST['min_teams'],
                'prize_pool' => (float)$_POST['prize_pool'],
                'entry_fee' => (float)$_POST['entry_fee'],
                'status' => $_POST['status'] ?? 'Upcoming',
                'venue' => sanitize($_POST['venue']),
                'rules' => sanitize($_POST['rules'])
            ];
            TournamentModel::update($id, $data);
            jsonResponse(['success' => true, 'message' => 'Tournament updated']);
            break;

        case 'delete':
            Auth::requireAdmin();
            $id = (int)($_GET['id'] ?? 0);
            TournamentModel::delete($id);
            jsonResponse(['success' => true, 'message' => 'Tournament deleted']);
            break;

        case 'categories':
            $categories = TournamentModel::getCategories();
            jsonResponse(['success' => true, 'data' => $categories]);
            break;

        case 'register':
            Auth::requireLogin();
            $tournamentId = (int)($_POST['tournament_id'] ?? 0);
            $userId = Auth::getUserId();
            $backUrl = '/GameArena/pages/tournaments.php' . ($tournamentId ? "?id=$tournamentId" : '');

            if (!$tournamentId) {
                header('Location: /GameArena/pages/tournaments.php?error=' . urlencode('Invalid tournament'));
                exit;
            }

            $db = getDB();

            $tournament = $db->fetchOne(
                "SELECT * FROM tournaments WHERE tournament_id = :id",
                [':id' => $tournamentId]
            );
            if (!$tournament) {
                header('Location: /GameArena/pages/tournaments.php?error=' . urlencode('Tournament not found'));
                exit;
            }
            if ($tournament['STATUS'] !== 'Upcoming') {
                header("Location: $backUrl&error=" . urlencode('Registration is closed for this tournament'));
                exit;
            }

            $team = $db->fetchOne(
                "SELECT t.team_id, t.team_name FROM teams t JOIN team_members tm ON t.team_id = tm.team_id WHERE tm.user_id = :user_id",
                [':user_id' => $userId]
            );
            if (!$team) {
                header("Location: $backUrl&error=" . urlencode('You must be in a team to register. Create or join a team first.'));
                exit;
            }

            $existing = $db->fetchOne(
                "SELECT registration_id FROM tournament_registrations WHERE tournament_id = :p_tid AND team_id = :p_team",
                [':p_tid' => $tournamentId, ':p_team' => $team['TEAM_ID']]
            );
            if ($existing) {
                header("Location: $backUrl&error=" . urlencode('Your team is already registered for this tournament'));
                exit;
            }

            $regCount = (int)$db->fetchColumn(
                "SELECT COUNT(*) FROM tournament_registrations WHERE tournament_id = :p_tid2",
                [':p_tid2' => $tournamentId]
            );
            if ($regCount >= $tournament['MAX_TEAMS']) {
                header("Location: $backUrl&error=" . urlencode('Tournament is full'));
                exit;
            }

            $newRegId = (int)$db->fetchColumn("SELECT NVL(MAX(registration_id), 0) + 1 FROM tournament_registrations");
            $db->insert(
                "INSERT INTO tournament_registrations (registration_id, tournament_id, team_id, registered_by, registration_date, status)
                 VALUES (:p_reg_id, :p_tid3, :p_team2, :p_uid, CURRENT_TIMESTAMP, 'Confirmed')",
                [':p_reg_id' => $newRegId, ':p_tid3' => $tournamentId, ':p_team2' => $team['TEAM_ID'], ':p_uid' => $userId]
            );

            header("Location: $backUrl&success=" . urlencode("Team {$team['TEAM_NAME']} registered successfully!"));
            exit;

        case 'registrations':
            $id = (int)($_GET['id'] ?? 0);
            $registrations = TournamentModel::getRegistrations($id);
            jsonResponse(['success' => true, 'data' => $registrations]);
            break;

        case 'stats':
            Auth::requireAdmin();
            $stats = TournamentModel::getStats();
            jsonResponse(['success' => true, 'data' => $stats]);
            break;

        default:
            jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
    }
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
}
