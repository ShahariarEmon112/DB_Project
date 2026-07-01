<?php
/**
 * GameArena - Auth Controller
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        $username = sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            header('Location: /GameArena/pages/login.php?error=Please fill all fields');
            exit;
        }

        $result = Auth::login($username, $password);

        if ($result['success']) {
            $user = Auth::getUser();
            if ($user['role_id'] == 1) {
                header('Location: /GameArena/admin/dashboard.php');
            } else {
                header('Location: /GameArena/pages/dashboard.php');
            }
        } else {
            header('Location: /GameArena/pages/login.php?error=' . urlencode($result['errors'][0]));
        }
        exit;

    case 'register':
        $result = Auth::register(
            sanitize($_POST['username'] ?? ''),
            $_POST['password'] ?? '',
            sanitize($_POST['full_name'] ?? ''),
            sanitize($_POST['phone'] ?? ''),
            sanitize($_POST['department'] ?? ''),
            sanitize($_POST['student_id'] ?? ''),
            sanitize($_POST['email'] ?? '')
        );

        if ($result['success']) {
            header('Location: /GameArena/pages/login.php?success=' . urlencode($result['message']));
        } else {
            header('Location: /GameArena/pages/register.php?error=' . urlencode($result['errors'][0]));
        }
        exit;

    case 'logout':
        Auth::logout();
        break;

    default:
        header('Location: /GameArena/pages/dashboard.php');
        exit;
}
