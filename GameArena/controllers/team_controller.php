<?php
/**
 * GameArena - Team Controller
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../models/TeamModel.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            $filters = [
                'department' => $_GET['department'] ?? '',
                'search' => $_GET['search'] ?? '',
                'sort' => $_GET['sort'] ?? 'team_name'
            ];
            $teams = TeamModel::getAll($filters);
            jsonResponse(['success' => true, 'data' => $teams]);
            break;

        case 'get':
            $id = (int)($_GET['id'] ?? 0);
            $team = TeamModel::getById($id);
            if ($team) {
                jsonResponse(['success' => true, 'data' => $team]);
            } else {
                jsonResponse(['success' => false, 'message' => 'Team not found'], 404);
            }
            break;

        case 'create':
            Auth::requireLogin();
            $data = [
                'team_name' => sanitize($_POST['team_name']),
                'description' => sanitize($_POST['description']),
                'department' => sanitize($_POST['department']),
                'captain_id' => Auth::getUserId()
            ];
            $id = TeamModel::create($data);
            jsonResponse(['success' => true, 'message' => 'Team created', 'id' => $id]);
            break;

        case 'update':
            Auth::requireAdmin();
            $id = (int)$_POST['team_id'];
            $data = [
                'team_name' => sanitize($_POST['team_name']),
                'description' => sanitize($_POST['description']),
                'department' => sanitize($_POST['department'])
            ];
            TeamModel::update($id, $data);
            jsonResponse(['success' => true, 'message' => 'Team updated']);
            break;

        case 'delete':
            Auth::requireAdmin();
            $id = (int)($_GET['id'] ?? 0);
            TeamModel::delete($id);
            jsonResponse(['success' => true, 'message' => 'Team deleted']);
            break;

        case 'members':
            $id = (int)($_GET['id'] ?? 0);
            $members = TeamModel::getMembers($id);
            jsonResponse(['success' => true, 'data' => $members]);
            break;

        case 'add_member':
            Auth::requireLogin();
            $teamId = (int)$_POST['team_id'];
            $userId = (int)$_POST['user_id'];
            $role = sanitize($_POST['role'] ?? 'Member');
            $result = TeamModel::addMember($teamId, $userId, $role);
            jsonResponse(['success' => $result, 'message' => $result ? 'Member added' : 'Failed to add member']);
            break;

        case 'remove_member':
            Auth::requireLogin();
            $teamId = (int)$_POST['team_id'];
            $userId = (int)$_POST['user_id'];
            $result = TeamModel::removeMember($teamId, $userId);
            jsonResponse(['success' => $result, 'message' => $result ? 'Member removed' : 'Failed to remove member']);
            break;

        case 'stats':
            Auth::requireAdmin();
            $stats = TeamModel::getStats();
            jsonResponse(['success' => true, 'data' => $stats]);
            break;

        case 'join':
            Auth::requireLogin();
            $teamId = (int)($_POST['team_id'] ?? 0);
            $userId = Auth::getUserId();
            $db = getDB();

            if (!$teamId) {
                header('Location: /GameArena/pages/teams.php?error=' . urlencode('Invalid team'));
                exit;
            }

            $alreadyMember = $db->fetchOne(
                "SELECT 1 FROM team_members WHERE team_id = :tid AND user_id = :p_user",
                [':tid' => $teamId, ':p_user' => $userId]
            );
            if ($alreadyMember) {
                header("Location: /GameArena/pages/teams.php?error=" . urlencode('You are already a member of this team'));
                exit;
            }

            $pending = $db->fetchOne(
                "SELECT 1 FROM team_join_requests WHERE team_id = :tid AND user_id = :p_user AND status = 'Pending'",
                [':tid' => $teamId, ':p_user' => $userId]
            );
            if ($pending) {
                header("Location: /GameArena/pages/teams.php?error=" . urlencode('You already have a pending request for this team'));
                exit;
            }

            $message = sanitize($_POST['message'] ?? '');
            $db->insert(
                "INSERT INTO team_join_requests (team_id, user_id, message, status, created_at)
                 VALUES (:tid, :p_user, :msg, 'Pending', CURRENT_TIMESTAMP)",
                [':tid' => $teamId, ':p_user' => $userId, ':msg' => $message]
            );

            header("Location: /GameArena/pages/teams.php?success=" . urlencode('Join request sent! Waiting for captain approval.'));
            exit;

        case 'approve_request':
            Auth::requireLogin();
            $requestId = (int)($_POST['request_id'] ?? 0);
            $userId = Auth::getUserId();
            $db = getDB();

            $req = $db->fetchOne(
                "SELECT r.*, t.captain_id FROM team_join_requests r
                 JOIN teams t ON r.team_id = t.team_id
                 WHERE r.request_id = :rid AND r.status = 'Pending'",
                [':rid' => $requestId]
            );
            if (!$req || $req['CAPTAIN_ID'] != $userId) {
                header("Location: /GameArena/pages/teams.php?error=" . urlencode('Unauthorized'));
                exit;
            }

            $db->update(
                "UPDATE team_join_requests SET status = 'Approved', responded_by = :p_user, responded_at = CURRENT_TIMESTAMP WHERE request_id = :rid",
                [':p_user' => $userId, ':rid' => $requestId]
            );

            $db->insert(
                "INSERT INTO team_members (team_id, user_id, role_in_team, joined_date) VALUES (:tid, :p_user, 'Member', CURRENT_TIMESTAMP)",
                [':tid' => $req['TEAM_ID'], ':p_user' => $req['USER_ID']]
            );

            header("Location: /GameArena/pages/teams.php?success=" . urlencode('Player approved and added to team!'));
            exit;

        case 'reject_request':
            Auth::requireLogin();
            $requestId = (int)($_POST['request_id'] ?? 0);
            $userId = Auth::getUserId();
            $db = getDB();

            $req = $db->fetchOne(
                "SELECT r.*, t.captain_id FROM team_join_requests r
                 JOIN teams t ON r.team_id = t.team_id
                 WHERE r.request_id = :rid AND r.status = 'Pending'",
                [':rid' => $requestId]
            );
            if (!$req || $req['CAPTAIN_ID'] != $userId) {
                header("Location: /GameArena/pages/teams.php?error=" . urlencode('Unauthorized'));
                exit;
            }

            $db->update(
                "UPDATE team_join_requests SET status = 'Rejected', responded_by = :p_user, responded_at = CURRENT_TIMESTAMP WHERE request_id = :rid",
                [':p_user' => $userId, ':rid' => $requestId]
            );

            header("Location: /GameArena/pages/teams.php?success=" . urlencode('Request rejected.'));
            exit;

        default:
            jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
    }
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
}
