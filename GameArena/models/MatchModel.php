<?php
/**
 * GameArena - Match Model
 */

require_once __DIR__ . '/../config/database.php';

class MatchModel {

    /**
     * Get all matches with optional filters
     */
    public static function getAll(array $filters = []): array {
        $db = getDB();
        $sql = "SELECT m.*, t1.team_name AS team1_name, t2.team_name AS team2_name,
                    trn.tournament_name, gc.category_name,
                    mr.team1_score, mr.team2_score,
                    tw.team_name AS winner_name,
                    mv.full_name AS mvp_name,
                    mr.duration_mins
                FROM matches m
                JOIN teams t1 ON m.team1_id = t1.team_id
                JOIN teams t2 ON m.team2_id = t2.team_id
                JOIN tournaments trn ON m.tournament_id = trn.tournament_id
                JOIN game_categories gc ON trn.category_id = gc.category_id
                LEFT JOIN match_results mr ON m.match_id = mr.match_id
                LEFT JOIN teams tw ON mr.winner_id = tw.team_id
                LEFT JOIN users mv ON mr.mvp_player_id = mv.user_id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND m.status = :status";
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['tournament'])) {
            $sql .= " AND m.tournament_id = :tournament";
            $params[':tournament'] = $filters['tournament'];
        }
        if (!empty($filters['team'])) {
            $sql .= " AND (m.team1_id = :team OR m.team2_id = :team)";
            $params[':team'] = $filters['team'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (UPPER(m.match_name) LIKE :search OR UPPER(t1.team_name) LIKE :search OR UPPER(t2.team_name) LIKE :search)";
            $params[':search'] = '%' . strtoupper($filters['search']) . '%';
        }

        $sort = !empty($filters['sort']) ? $filters['sort'] : 'match_date';
        $direction = !empty($filters['direction']) ? $filters['direction'] : 'DESC';
        $allowedSorts = ['match_date', 'status', 'created_at'];
        if (in_array($sort, $allowedSorts)) {
            $sql .= " ORDER BY m.{$sort} {$direction}";
        } else {
            $sql .= " ORDER BY m.match_date DESC";
        }

        return $db->fetchAll($sql, $params);
    }

    /**
     * Get upcoming matches
     */
    public static function getUpcoming(int $limit = 10): array {
        $db = getDB();
        return $db->fetchAll(
            "SELECT m.*, t1.team_name AS team1_name, t2.team_name AS team2_name,
                    trn.tournament_name, gc.category_name
             FROM matches m
             JOIN teams t1 ON m.team1_id = t1.team_id
             JOIN teams t2 ON m.team2_id = t2.team_id
             JOIN tournaments trn ON m.tournament_id = trn.tournament_id
             JOIN game_categories gc ON trn.category_id = gc.category_id
             WHERE m.status IN ('Scheduled', 'Live')
             ORDER BY m.match_date ASC
             FETCH FIRST :limit ROWS ONLY",
            [':limit' => $limit]
        );
    }

    /**
     * Get match by ID
     */
    public static function getById(int $id): ?array {
        $db = getDB();
        return $db->fetchOne(
            "SELECT m.*, t1.team_name AS team1_name, t2.team_name AS team2_name,
                    trn.tournament_name, gc.category_name,
                    mr.team1_score, mr.team2_score, mr.winner_id, mr.mvp_player_id,
                    mr.duration_mins, mr.notes,
                    tw.team_name AS winner_name,
                    mv.full_name AS mvp_name,
                    rec.full_name AS recorded_by_name
             FROM matches m
             JOIN teams t1 ON m.team1_id = t1.team_id
             JOIN teams t2 ON m.team2_id = t2.team_id
             JOIN tournaments trn ON m.tournament_id = trn.tournament_id
             JOIN game_categories gc ON trn.category_id = gc.category_id
             LEFT JOIN match_results mr ON m.match_id = mr.match_id
             LEFT JOIN teams tw ON mr.winner_id = tw.team_id
             LEFT JOIN users mv ON mr.mvp_player_id = mv.user_id
             LEFT JOIN users rec ON mr.recorded_by = rec.user_id
             WHERE m.match_id = :id",
            [':id' => $id]
        );
    }

    /**
     * Create match
     */
    public static function create(array $data): int {
        $db = getDB();
        $sql = "INSERT INTO matches (tournament_id, match_name, team1_id, team2_id, match_date, match_time, venue, round, status)
                VALUES (:tournament, :name, :team1, :team2, :date, :time, :venue, :round, 'Scheduled')";

        $db->insert($sql, [
            ':tournament' => $data['tournament_id'],
            ':name' => $data['match_name'],
            ':team1' => $data['team1_id'],
            ':team2' => $data['team2_id'],
            ':date' => $data['match_date'],
            ':time' => $data['match_time'] ?? '',
            ':venue' => $data['venue'] ?? '',
            ':round' => $data['round'] ?? 'Group Stage'
        ]);

        return (int)$db->fetchColumn("SELECT MAX(match_id) FROM matches");
    }

    /**
     * Update match
     */
    public static function update(int $id, array $data): bool {
        $db = getDB();
        $sql = "UPDATE matches SET
                    match_name = :name,
                    tournament_id = :tournament,
                    team1_id = :team1,
                    team2_id = :team2,
                    match_date = :date,
                    match_time = :time,
                    venue = :venue,
                    round = :round,
                    status = :status,
                    updated_at = CURRENT_TIMESTAMP
                WHERE match_id = :id";

        return $db->update($sql, [
            ':id' => $id,
            ':name' => $data['match_name'],
            ':tournament' => $data['tournament_id'],
            ':team1' => $data['team1_id'],
            ':team2' => $data['team2_id'],
            ':date' => $data['match_date'],
            ':time' => $data['match_time'] ?? '',
            ':venue' => $data['venue'] ?? '',
            ':round' => $data['round'] ?? 'Group Stage',
            ':status' => $data['status'] ?? 'Scheduled'
        ]);
    }

    /**
     * Update match result
     */
    public static function updateResult(int $matchId, array $data): bool {
        $db = getDB();
        try {
            $db->beginTransaction();

            $sql = "INSERT INTO match_results (match_id, team1_score, team2_score, winner_id, mvp_player_id, duration_mins, notes, recorded_by)
                    VALUES (:match, :score1, :score2, :winner, :mvp, :duration, :notes, :recorded_by)";

            $db->insert($sql, [
                ':match' => $matchId,
                ':score1' => $data['team1_score'] ?? 0,
                ':score2' => $data['team2_score'] ?? 0,
                ':winner' => $data['winner_id'] ?? null,
                ':mvp' => $data['mvp_player_id'] ?? null,
                ':duration' => $data['duration_mins'] ?? null,
                ':notes' => $data['notes'] ?? '',
                ':recorded_by' => $data['recorded_by']
            ]);

            $db->update(
                "UPDATE matches SET status = 'Completed' WHERE match_id = :id",
                [':id' => $matchId]
            );

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    /**
     * Delete match
     */
    public static function delete(int $id): bool {
        $db = getDB();
        return $db->delete(
            "DELETE FROM matches WHERE match_id = :id",
            [':id' => $id]
        );
    }

    /**
     * Get match stats
     */
    public static function getStats(): array {
        $db = getDB();
        return [
            'total' => (int)$db->fetchColumn("SELECT COUNT(*) FROM matches"),
            'scheduled' => (int)$db->fetchColumn("SELECT COUNT(*) FROM matches WHERE status = 'Scheduled'"),
            'live' => (int)$db->fetchColumn("SELECT COUNT(*) FROM matches WHERE status = 'Live'"),
            'completed' => (int)$db->fetchColumn("SELECT COUNT(*) FROM matches WHERE status = 'Completed'"),
            'cancelled' => (int)$db->fetchColumn("SELECT COUNT(*) FROM matches WHERE status = 'Cancelled'")
        ];
    }
}
