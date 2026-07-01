<?php
/**
 * GameArena - User Model
 */

require_once __DIR__ . '/../config/database.php';

class UserModel {

    /**
     * Get all users with optional filters
     */
    public static function getAll(array $filters = []): array {
        $db = getDB();
        $sql = "SELECT u.*, r.role_name
                FROM users u
                JOIN roles r ON u.role_id = r.role_id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['role'])) {
            $sql .= " AND u.role_id = :role";
            $params[':role'] = $filters['role'];
        }
        if (!empty($filters['department'])) {
            $sql .= " AND u.department = :department";
            $params[':department'] = $filters['department'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (UPPER(u.full_name) LIKE :search OR UPPER(u.username) LIKE :search OR UPPER(u.email) LIKE :search)";
            $params[':search'] = '%' . strtoupper($filters['search']) . '%';
        }

        $sort = !empty($filters['sort']) ? $filters['sort'] : 'created_at';
        $direction = !empty($filters['direction']) ? $filters['direction'] : 'DESC';
        $allowedSorts = ['full_name', 'username', 'email', 'created_at', 'last_login'];
        if (in_array($sort, $allowedSorts)) {
            $sql .= " ORDER BY u.{$sort} {$direction}";
        } else {
            $sql .= " ORDER BY u.created_at DESC";
        }

        return $db->fetchAll($sql, $params);
    }

    /**
     * Get user by ID
     */
    public static function getById(int $id): ?array {
        $db = getDB();
        return $db->fetchOne(
            "SELECT u.*, r.role_name
             FROM users u
             JOIN roles r ON u.role_id = r.role_id
             WHERE u.user_id = :id",
            [':id' => $id]
        );
    }

    /**
     * Get player stats
     */
    public static function getPlayerStats(int $userId): array {
        $db = getDB();
        $stats = $db->fetchOne(
            "SELECT NVL(SUM(matches_played), 0) AS total_matches,
                    NVL(SUM(wins), 0) AS total_wins,
                    NVL(SUM(losses), 0) AS total_losses,
                    NVL(SUM(kills), 0) AS total_kills,
                    NVL(SUM(deaths), 0) AS total_deaths,
                    NVL(SUM(assists), 0) AS total_assists,
                    NVL(SUM(mvp_count), 0) AS total_mvps,
                    NVL(SUM(points), 0) AS total_points
             FROM player_statistics
             WHERE player_id = :id",
            [':id' => $userId]
        );

        $team = $db->fetchOne(
            "SELECT t.team_id, t.team_name
             FROM teams t
             JOIN team_members tm ON t.team_id = tm.team_id
             WHERE tm.user_id = :id",
            [':id' => $userId]
        );

        $stats['team'] = $team;
        $stats['kd_ratio'] = $stats['total_deaths'] > 0
            ? round($stats['total_kills'] / $stats['total_deaths'], 2)
            : $stats['total_kills'];

        return $stats;
    }

    /**
     * Create user
     */
    public static function create(array $data): int {
        $db = getDB();
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (username, email, password, full_name, phone, department, student_id, role_id)
                VALUES (:username, :email, :password, :full_name, :phone, :department, :student_id, :role)";

        $db->insert($sql, [
            ':username' => $data['username'],
            ':email' => $data['email'],
            ':password' => $hashedPassword,
            ':full_name' => $data['full_name'],
            ':phone' => $data['phone'] ?? '',
            ':department' => $data['department'] ?? '',
            ':student_id' => $data['student_id'] ?? '',
            ':role' => $data['role_id'] ?? 2
        ]);

        return (int)$db->fetchColumn("SELECT MAX(user_id) FROM users");
    }

    /**
     * Update user
     */
    public static function update(int $id, array $data): bool {
        $db = getDB();
        $sql = "UPDATE users SET
                    full_name = :full_name,
                    email = :email,
                    phone = :phone,
                    department = :department,
                    student_id = :student_id,
                    role_id = :role,
                    is_active = :active,
                    updated_at = CURRENT_TIMESTAMP
                WHERE user_id = :id";

        return $db->update($sql, [
            ':id' => $id,
            ':full_name' => $data['full_name'],
            ':email' => $data['email'],
            ':phone' => $data['phone'] ?? '',
            ':department' => $data['department'] ?? '',
            ':student_id' => $data['student_id'] ?? '',
            ':role' => $data['role_id'] ?? 2,
            ':active' => $data['is_active'] ?? 1
        ]);
    }

    /**
     * Delete user
     */
    public static function delete(int $id): bool {
        $db = getDB();
        return $db->delete(
            "DELETE FROM users WHERE user_id = :id",
            [':id' => $id]
        );
    }

    /**
     * Get user stats
     */
    public static function getStats(): array {
        $db = getDB();
        return [
            'total' => (int)$db->fetchColumn("SELECT COUNT(*) FROM users"),
            'players' => (int)$db->fetchColumn("SELECT COUNT(*) FROM users WHERE role_id = 2"),
            'captains' => (int)$db->fetchColumn("SELECT COUNT(*) FROM users WHERE role_id = 3"),
            'admins' => (int)$db->fetchColumn("SELECT COUNT(*) FROM users WHERE role_id = 1")
        ];
    }

    /**
     * Get roles
     */
    public static function getRoles(): array {
        $db = getDB();
        return $db->fetchAll("SELECT * FROM roles ORDER BY role_id");
    }
}
