<?php
/**
 * GameArena - User Controller
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../models/UserModel.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            Auth::requireAdmin();
            $filters = [
                'role' => $_GET['role'] ?? '',
                'department' => $_GET['department'] ?? '',
                'search' => $_GET['search'] ?? '',
                'sort' => $_GET['sort'] ?? 'created_at',
                'direction' => $_GET['direction'] ?? 'DESC'
            ];
            $users = UserModel::getAll($filters);
            jsonResponse(['success' => true, 'data' => $users]);
            break;

        case 'get':
            $id = (int)($_GET['id'] ?? 0);
            $user = UserModel::getById($id);
            if ($user) {
                jsonResponse(['success' => true, 'data' => $user]);
            } else {
                jsonResponse(['success' => false, 'message' => 'User not found'], 404);
            }
            break;

        case 'create':
            Auth::requireAdmin();
            $data = [
                'username' => sanitize($_POST['username']),
                'email' => sanitize($_POST['email']),
                'password' => $_POST['password'],
                'full_name' => sanitize($_POST['full_name']),
                'phone' => sanitize($_POST['phone']),
                'department' => sanitize($_POST['department']),
                'student_id' => sanitize($_POST['student_id']),
                'role_id' => (int)$_POST['role_id']
            ];
            $id = UserModel::create($data);
            jsonResponse(['success' => true, 'message' => 'User created', 'id' => $id]);
            break;

        case 'update':
            Auth::requireAdmin();
            $id = (int)$_POST['user_id'];
            $data = [
                'full_name' => sanitize($_POST['full_name']),
                'email' => sanitize($_POST['email']),
                'phone' => sanitize($_POST['phone']),
                'department' => sanitize($_POST['department']),
                'student_id' => sanitize($_POST['student_id']),
                'role_id' => (int)$_POST['role_id'],
                'is_active' => (int)($_POST['is_active'] ?? 1)
            ];
            UserModel::update($id, $data);
            jsonResponse(['success' => true, 'message' => 'User updated']);
            break;

        case 'delete':
            Auth::requireAdmin();
            $id = (int)($_GET['id'] ?? 0);
            UserModel::delete($id);
            jsonResponse(['success' => true, 'message' => 'User deleted']);
            break;

        case 'player_stats':
            $id = (int)($_GET['id'] ?? 0);
            $stats = UserModel::getPlayerStats($id);
            jsonResponse(['success' => true, 'data' => $stats]);
            break;

        case 'stats':
            Auth::requireAdmin();
            $stats = UserModel::getStats();
            jsonResponse(['success' => true, 'data' => $stats]);
            break;

        case 'roles':
            $roles = UserModel::getRoles();
            jsonResponse(['success' => true, 'data' => $roles]);
            break;

        default:
            jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
    }
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
}
