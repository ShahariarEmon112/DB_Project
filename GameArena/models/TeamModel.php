<?php
/**
 * GameArena - Team Model
 */

require_once __DIR__ . '/../config/database.php';

class TeamModel {

    /**
     * Get all teams with optional filters
     */
    public static function getAll(array $filters = []): array {
        $db = getDB();
        $sql = "SELECT t.*, u.full_name AS captain_name,
                    (SELECT COUNT(*) FROM team_members tm WHERE tm.team_id = t.team_id) AS member_count,
                    NVL(GET_TEAM_WIN_RATE(t.team_id), 0) AS win_rate,
                    GET_TOTAL_MATCHES(t.team_id) AS total_matches
                FROM teams t
                LEFT JOIN users u ON t.captain_id = u.user_id
                WHERE t.is_active = 1";
        $params = [];

        if (!empty($filters['department'])) {
            $sql .= " AND t.department = :department";
            $params[':department'] = $filters['department'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND UPPER(t.team_name) LIKE :search";
            $params[':search'] = '%' . strtoupper($filters['search']) . '%';
        }

        $sort = !empty($filters['sort']) ? $filters['sort'] : 'team_name';
        $allowedSorts = ['team_name', 'created_at', 'win_rate', 'total_matches'];
        if (in_array($sort, $allowedSorts)) {
            $sql .= " ORDER BY t.{$sort}";
        } else {
            $sql .= " ORDER BY t.team_name";
        }

        return $db->fetchAll($sql, $params);
    }

    /**
     * Get team by ID
     */
    public static function getById(int $id): ?array {
        $db = getDB();
        return $db->fetchOne(
            "SELECT t.*, u.full_name AS captain_name,
                    (SELECT COUNT(*) FROM team_members tm WHERE tm.team_id = t.team_id) AS member_count,
                    NVL(GET_TEAM_WIN_RATE(t.team_id), 0) AS win_rate,
                    GET_TOTAL_MATCHES(t.team_id) AS total_matches
             FROM teams t
             LEFT JOIN users u ON t.captain_id = u.user_id
             WHERE t.team_id = :id",
            [':id' => $id]
        );
    }

    /**
     * Create team
     */
    public static function create(array $data): int {
        $db = getDB();
        $sql = "INSERT INTO teams (team_name, description, department, captain_id, logo)
                VALUES (:name, :description, :department, :captain, :logo)";

        $db->insert($sql, [
            ':name' => $data['team_name'],
            ':description' => $data['description'] ?? '',
            ':department' => $data['department'] ?? '',
            ':captain' => $data['captain_id'],
            ':logo' => $data['logo'] ?? 'default_team.png'
        ]);

        $teamId = (int)$db->fetchColumn("SELECT MAX(team_id) FROM teams");

        // Add captain as member
        if ($teamId) {
            $db->insert(
                "INSERT INTO team_members (team_id, user_id, role_in_team) VALUES (:team, :user, 'Captain')",
                [':team' => $teamId, ':user' => $data['captain_id']]
            );
        }

        return $teamId;
    }

    /**
     * Update team
     */
    public static function update(int $id, array $data): bool {
        $db = getDB();
        $sql = "UPDATE teams SET
                    team_name = :name,
                    description = :description,
                    department = :department,
                    updated_at = CURRENT_TIMESTAMP
                WHERE team_id = :id";

        return $db->update($sql, [
            ':id' => $id,
            ':name' => $data['team_name'],
            ':description' => $data['description'] ?? '',
            ':department' => $data['department'] ?? ''
        ]);
    }

    /**
     * Delete team
     */
    public static function delete(int $id): bool {
        $db = getDB();
        return $db->delete(
            "DELETE FROM teams WHERE team_id = :id",
            [':id' => $id]
        );
    }

    /**
     * Add member to team
     */
    public static function addMember(int $teamId, int $userId, string $role = 'Member'): bool {
        $db = getDB();
        try {
            $db->insert(
                "INSERT INTO team_members (team_id, user_id, role_in_team) VALUES (:team, :user, :role)",
                [':team' => $teamId, ':user' => $userId, ':role' => $role]
            );
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Remove member from team
     */
    public static function removeMember(int $teamId, int $userId): bool {
        $db = getDB();
        return $db->delete(
            "DELETE FROM team_members WHERE team_id = :team AND user_id = :user",
            [':team' => $teamId, ':user' => $userId]
        );
    }

    /**
     * Get team members
     */
    public static function getMembers(int $teamId): array {
        $db = getDB();
        return $db->fetchAll(
            "SELECT tm.*, u.username, u.full_name, u.email, u.department, u.student_id
             FROM team_members tm
             JOIN users u ON tm.user_id = u.user_id
             WHERE tm.team_id = :team
             ORDER BY tm.role_in_team, u.full_name",
            [':team' => $teamId]
        );
    }

    /**
     * Get team stats
     */
    public static function getStats(): array {
        $db = getDB();
        return [
            'total' => (int)$db->fetchColumn("SELECT COUNT(*) FROM teams WHERE is_active = 1"),
            'departments' => (int)$db->fetchColumn("SELECT COUNT(DISTINCT department) FROM teams WHERE is_active = 1 AND department IS NOT NULL")
        ];
    }
}
