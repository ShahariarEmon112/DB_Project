<?php
/**
 * GameArena - Tournament Model
 */

require_once __DIR__ . '/../config/database.php';

class TournamentModel {

    /**
     * Get all tournaments with optional filters
     */
    public static function getAll(array $filters = []): array {
        $db = getDB();
        $sql = "SELECT t.*, gc.category_name, gc.icon AS category_icon, u.full_name AS organizer_name
                FROM tournaments t
                JOIN game_categories gc ON t.category_id = gc.category_id
                LEFT JOIN users u ON t.created_by = u.user_id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND t.status = :status";
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['category'])) {
            $sql .= " AND t.category_id = :category";
            $params[':category'] = $filters['category'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND UPPER(t.tournament_name) LIKE :search";
            $params[':search'] = '%' . strtoupper($filters['search']) . '%';
        }

        $sort = !empty($filters['sort']) ? $filters['sort'] : 'start_date';
        $direction = !empty($filters['direction']) ? $filters['direction'] : 'DESC';
        $allowedSorts = ['tournament_name', 'start_date', 'prize_pool', 'status', 'created_at'];
        if (in_array($sort, $allowedSorts)) {
            $sql .= " ORDER BY t.{$sort} {$direction}";
        } else {
            $sql .= " ORDER BY t.start_date DESC";
        }

        return $db->fetchAll($sql, $params);
    }

    /**
     * Get active tournaments
     */
    public static function getActive(): array {
        $db = getDB();
        return $db->fetchAll(
            "SELECT t.*, gc.category_name, gc.icon AS category_icon
             FROM tournaments t
             JOIN game_categories gc ON t.category_id = gc.category_id
             WHERE t.status IN ('Active', 'Upcoming')
             ORDER BY t.start_date ASC"
        );
    }

    /**
     * Get tournament by ID
     */
    public static function getById(int $id): ?array {
        $db = getDB();
        return $db->fetchOne(
            "SELECT t.*, gc.category_name, gc.icon AS category_icon, u.full_name AS organizer_name
             FROM tournaments t
             JOIN game_categories gc ON t.category_id = gc.category_id
             LEFT JOIN users u ON t.created_by = u.user_id
             WHERE t.tournament_id = :id",
            [':id' => $id]
        );
    }

    /**
     * Get tournament with registration count
     */
    public static function getWithStats(int $id): ?array {
        $db = getDB();
        return $db->fetchOne(
            "SELECT t.*, gc.category_name,
                    (SELECT COUNT(*) FROM tournament_registrations tr WHERE tr.tournament_id = t.tournament_id AND tr.status = 'Confirmed') AS registered_teams,
                    (SELECT COUNT(*) FROM matches m WHERE m.tournament_id = t.tournament_id) AS total_matches,
                    (SELECT COUNT(*) FROM matches m WHERE m.tournament_id = t.tournament_id AND m.status = 'Completed') AS completed_matches
             FROM tournaments t
             JOIN game_categories gc ON t.category_id = gc.category_id
             WHERE t.tournament_id = :id",
            [':id' => $id]
        );
    }

    /**
     * Create tournament
     */
    public static function create(array $data): int {
        $db = getDB();
        $sql = "INSERT INTO tournaments (tournament_name, category_id, description, start_date, end_date, registration_deadline, max_teams, min_teams, prize_pool, entry_fee, status, venue, rules, created_by)
                VALUES (:name, :category, :description, :start_date, :end_date, :deadline, :max_teams, :min_teams, :prize, :fee, :status, :venue, :rules, :created_by)";

        $db->insert($sql, [
            ':name' => $data['tournament_name'],
            ':category' => $data['category_id'],
            ':description' => $data['description'] ?? '',
            ':start_date' => $data['start_date'],
            ':end_date' => $data['end_date'] ?? null,
            ':deadline' => $data['registration_deadline'],
            ':max_teams' => $data['max_teams'] ?? 16,
            ':min_teams' => $data['min_teams'] ?? 4,
            ':prize' => $data['prize_pool'] ?? 0,
            ':fee' => $data['entry_fee'] ?? 0,
            ':status' => $data['status'] ?? 'Upcoming',
            ':venue' => $data['venue'] ?? '',
            ':rules' => $data['rules'] ?? '',
            ':created_by' => $data['created_by']
        ]);

        return (int)$db->fetchColumn("SELECT MAX(tournament_id) FROM tournaments");
    }

    /**
     * Update tournament
     */
    public static function update(int $id, array $data): bool {
        $db = getDB();
        $sql = "UPDATE tournaments SET
                    tournament_name = :name,
                    category_id = :category,
                    description = :description,
                    start_date = :start_date,
                    end_date = :end_date,
                    registration_deadline = :deadline,
                    max_teams = :max_teams,
                    min_teams = :min_teams,
                    prize_pool = :prize,
                    entry_fee = :fee,
                    status = :status,
                    venue = :venue,
                    rules = :rules,
                    updated_at = CURRENT_TIMESTAMP
                WHERE tournament_id = :id";

        return $db->update($sql, [
            ':id' => $id,
            ':name' => $data['tournament_name'],
            ':category' => $data['category_id'],
            ':description' => $data['description'] ?? '',
            ':start_date' => $data['start_date'],
            ':end_date' => $data['end_date'] ?? null,
            ':deadline' => $data['registration_deadline'],
            ':max_teams' => $data['max_teams'] ?? 16,
            ':min_teams' => $data['min_teams'] ?? 4,
            ':prize' => $data['prize_pool'] ?? 0,
            ':fee' => $data['entry_fee'] ?? 0,
            ':status' => $data['status'] ?? 'Upcoming',
            ':venue' => $data['venue'] ?? '',
            ':rules' => $data['rules'] ?? ''
        ]);
    }

    /**
     * Delete tournament
     */
    public static function delete(int $id): bool {
        $db = getDB();
        return $db->delete(
            "DELETE FROM tournaments WHERE tournament_id = :id",
            [':id' => $id]
        );
    }

    /**
     * Get categories
     */
    public static function getCategories(): array {
        $db = getDB();
        return $db->fetchAll(
            "SELECT * FROM game_categories WHERE is_active = 1 ORDER BY category_name"
        );
    }

    /**
     * Get tournament registrations
     */
    public static function getRegistrations(int $tournamentId): array {
        $db = getDB();
        return $db->fetchAll(
            "SELECT tr.*, t.team_name, u.full_name AS registered_by_name
             FROM tournament_registrations tr
             JOIN teams t ON tr.team_id = t.team_id
             LEFT JOIN users u ON tr.registered_by = u.user_id
             WHERE tr.tournament_id = :id
             ORDER BY tr.registration_date DESC",
            [':id' => $tournamentId]
        );
    }

    /**
     * Get tournament stats
     */
    public static function getStats(): array {
        $db = getDB();
        return [
            'total' => (int)$db->fetchColumn("SELECT COUNT(*) FROM tournaments"),
            'active' => (int)$db->fetchColumn("SELECT COUNT(*) FROM tournaments WHERE status = 'Active'"),
            'upcoming' => (int)$db->fetchColumn("SELECT COUNT(*) FROM tournaments WHERE status = 'Upcoming'"),
            'completed' => (int)$db->fetchColumn("SELECT COUNT(*) FROM tournaments WHERE status = 'Completed'"),
            'total_prize' => (float)$db->fetchColumn("SELECT NVL(SUM(prize_pool), 0) FROM tournaments")
        ];
    }
}
