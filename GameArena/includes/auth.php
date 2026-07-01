<?php
/**
 * GameArena - Authentication System
 * Handles Login, Register, Logout, Session Management
 */

require_once __DIR__ . '/../config/database.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Authentication Class
 */
class Auth {

    /**
     * Register a new user
     */
    public static function register(
        string $username,
        string $password,
        string $fullName,
        string $phone = '',
        string $department = '',
        string $studentId = '',
        string $email = ''
    ): array {
        $db = getDB();

        // Validate inputs
        $errors = [];

        if (strlen($username) < 3) {
            $errors[] = 'Username must be at least 3 characters.';
        }
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = 'Username can only contain letters, numbers, and underscores.';
        }
        if (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        }
        if (empty($fullName)) {
            $errors[] = 'Full name is required.';
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Check if username exists
        $existing = $db->fetchOne(
            "SELECT user_id FROM users WHERE username = :username",
            [':username' => $username]
        );

        if ($existing) {
            return ['success' => false, 'errors' => ['Username already exists.']];
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert user
        try {
            $sql = "INSERT INTO users (username, password, full_name, phone, department, student_id, role_id, is_active)
                    VALUES (:username, :password, :full_name, :phone, :department, :student_id, 2, 1)";

            $db->insert($sql, [
                ':username' => $username,
                ':password' => $hashedPassword,
                ':full_name' => $fullName,
                ':phone' => $phone,
                ':department' => $department,
                ':student_id' => $studentId
            ]);

            return ['success' => true, 'message' => 'Registration successful! Please login.'];

        } catch (Exception $e) {
            return ['success' => false, 'errors' => ['Registration failed: ' . $e->getMessage()]];
        }
    }

    /**
     * Login user
     */
    public static function login(string $username, string $password): array {
        $db = getDB();

        // Get user
        $user = $db->fetchOne(
            "SELECT u.*, r.role_name FROM users u
             JOIN roles r ON u.role_id = r.role_id
             WHERE u.username = :input AND u.is_active = 1",
            [':input' => $username]
        );

        if (!$user) {
            return ['success' => false, 'errors' => ['Invalid username or password.']];
        }

        // Verify password
        if (!password_verify($password, $user['PASSWORD'])) {
            return ['success' => false, 'errors' => ['Invalid username or password.']];
        }

        // Update last login
        $db->update(
            "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE user_id = :id",
            [':id' => $user['USER_ID']]
        );

        // Set session
        $_SESSION['user_id'] = $user['USER_ID'];
        $_SESSION['username'] = $user['USERNAME'];
        $_SESSION['full_name'] = $user['FULL_NAME'];
        $_SESSION['email'] = $user['EMAIL'];
        $_SESSION['role_id'] = $user['ROLE_ID'];
        $_SESSION['role_name'] = $user['ROLE_NAME'];
        $_SESSION['department'] = $user['DEPARTMENT'];
        $_SESSION['logged_in'] = true;

        return [
            'success' => true,
            'message' => 'Login successful!',
            'user' => [
                'id' => $user['USER_ID'],
                'username' => $user['USERNAME'],
                'full_name' => $user['FULL_NAME'],
                'email' => $user['EMAIL'],
                'role' => $user['ROLE_NAME'],
                'department' => $user['DEPARTMENT']
            ]
        ];
    }

    /**
     * Logout user
     */
    public static function logout(): void {
        session_unset();
        session_destroy();
        header('Location: /GameArena/pages/login.php');
        exit;
    }

    /**
     * Check if user is logged in
     */
    public static function isLoggedIn(): bool {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    /**
     * Check if user is admin
     */
    public static function isAdmin(): bool {
        return self::isLoggedIn() && isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1;
    }

    /**
     * Check if user is captain
     */
    public static function isCaptain(): bool {
        return self::isLoggedIn() && isset($_SESSION['role_id']) && $_SESSION['role_id'] == 3;
    }

    /**
     * Check if user is organizer
     */
    public static function isOrganizer(): bool {
        return self::isLoggedIn() && isset($_SESSION['role_id']) && $_SESSION['role_id'] == 4;
    }

    /**
     * Require login
     */
    public static function requireLogin(): void {
        if (!self::isLoggedIn()) {
            header('Location: /GameArena/pages/login.php');
            exit;
        }
    }

    /**
     * Require admin
     */
    public static function requireAdmin(): void {
        if (!self::isAdmin()) {
            header('Location: /GameArena/pages/login.php');
            exit;
        }
    }

    /**
     * Get current user data
     */
    public static function getUser(): ?array {
        if (!self::isLoggedIn()) return null;

        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'full_name' => $_SESSION['full_name'],
            'email' => $_SESSION['email'],
            'role_id' => $_SESSION['role_id'],
            'role_name' => $_SESSION['role_name'],
            'department' => $_SESSION['department']
        ];
    }

    /**
     * Get user ID
     */
    public static function getUserId(): ?int {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Change password
     */
    public static function changePassword(int $userId, string $currentPassword, string $newPassword): array {
        $db = getDB();

        $user = $db->fetchOne(
            "SELECT password FROM users WHERE user_id = :id",
            [':id' => $userId]
        );

        if (!$user || !password_verify($currentPassword, $user['PASSWORD'])) {
            return ['success' => false, 'errors' => ['Current password is incorrect.']];
        }

        if (strlen($newPassword) < 6) {
            return ['success' => false, 'errors' => ['New password must be at least 6 characters.']];
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        try {
            $db->update(
                "UPDATE users SET password = :password, updated_at = CURRENT_TIMESTAMP WHERE user_id = :id",
                [':password' => $hashedPassword, ':id' => $userId]
            );
            return ['success' => true, 'message' => 'Password changed successfully.'];
        } catch (Exception $e) {
            return ['success' => false, 'errors' => ['Failed to change password: ' . $e->getMessage()]];
        }
    }

    /**
     * Get user's team
     */
    public static function getUserTeam(): ?array {
        if (!self::isLoggedIn()) return null;

        $db = getDB();
        return $db->fetchOne(
            "SELECT t.* FROM teams t
             JOIN team_members tm ON t.team_id = tm.team_id
             WHERE tm.user_id = :user_id",
            [':user_id' => self::getUserId()]
        );
    }
}
