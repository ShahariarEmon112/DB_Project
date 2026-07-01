<?php
/**
 * GameArena - Leaderboard Page
 */
$pageTitle = 'Leaderboard';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

$db = getDB();

// Get active tournaments
$activeTournaments = $db->fetchAll(
    "SELECT tournament_id, tournament_name FROM tournaments WHERE status IN ('Active', 'Completed') ORDER BY tournament_name"
);

// Get selected tournament
$tournamentId = $_GET['tournament'] ?? '';
if (!$tournamentId && !empty($activeTournaments)) {
    $tournamentId = $activeTournaments[0]['TOURNAMENT_ID'];
}

// Get leaderboard
$leaderboard = [];
if ($tournamentId) {
    $leaderboard = $db->fetchAll(
        "SELECT lb.*, t.team_name, t.department,
                NVL(GET_TEAM_WIN_RATE(t.team_id), 0) AS win_rate
         FROM leaderboard lb
         JOIN teams t ON lb.team_id = t.team_id
         WHERE lb.tournament_id = :tournament
         ORDER BY lb.rank_position ASC",
        [':tournament' => $tournamentId]
    );
}

// Get player leaderboard
$playerStats = $db->fetchAll(
    "SELECT u.user_id, u.full_name, u.department, u.student_id,
            NVL(SUM(ps.matches_played), 0) AS total_matches,
            NVL(SUM(ps.wins), 0) AS total_wins,
            NVL(SUM(ps.kills), 0) AS total_kills,
            NVL(SUM(ps.mvp_count), 0) AS total_mvps,
            NVL(SUM(ps.points), 0) AS total_points
     FROM users u
     LEFT JOIN player_statistics ps ON u.user_id = ps.player_id
     WHERE u.role_id != 1
     GROUP BY u.user_id, u.full_name, u.department, u.student_id
     ORDER BY total_points DESC
     FETCH FIRST 10 ROWS ONLY"
);

require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold"><i class="fas fa-ranking-star me-2 text-warning"></i>Leaderboard</h2>
            <p class="text-muted">See the top teams and players</p>
        </div>
    </div>

    <!-- Tournament Filter -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="d-flex gap-2">
                        <select name="tournament" class="form-select">
                            <?php foreach ($activeTournaments as $t): ?>
                                <option value="<?= $t['TOURNAMENT_ID'] ?>" <?= $tournamentId == $t['TOURNAMENT_ID'] ? 'selected' : '' ?>>
                                    <?= sanitize($t['TOURNAMENT_NAME']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-1"></i>View
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Team Leaderboard -->
        <div class="col-lg-7 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-shield-halved me-2 text-primary"></i>Team Rankings</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($leaderboard)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-ranking-star fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No leaderboard data for this tournament.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover leaderboard-table">
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Team</th>
                                        <th>Department</th>
                                        <th>Points</th>
                                        <th>W/L</th>
                                        <th>Win Rate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($leaderboard as $i => $entry): ?>
                                        <tr class="rank-<?= $entry['RANK_POSITION'] ?>">
                                            <td>
                                                <?php if ($entry['RANK_POSITION'] == 1): ?>
                                                    <span class="rank-badge gold">1</span>
                                                <?php elseif ($entry['RANK_POSITION'] == 2): ?>
                                                    <span class="rank-badge silver">2</span>
                                                <?php elseif ($entry['RANK_POSITION'] == 3): ?>
                                                    <span class="rank-badge bronze">3</span>
                                                <?php else: ?>
                                                    <span class="rank-badge"><?= $entry['RANK_POSITION'] ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td><strong><?= sanitize($entry['TEAM_NAME']) ?></strong></td>
                                            <td><small class="text-muted"><?= sanitize($entry['DEPARTMENT'] ?: 'General') ?></small></td>
                                            <td><span class="text-warning fw-bold"><?= $entry['POINTS'] ?></span></td>
                                            <td>
                                                <span class="text-success"><?= $entry['WINS'] ?>W</span> /
                                                <span class="text-danger"><?= $entry['LOSSES'] ?>L</span>
                                            </td>
                                            <td>
                                                <div class="progress" style="height: 20px; width: 100px;">
                                                    <div class="progress-bar bg-success" style="width: <?= $entry['WIN_RATE'] ?>%">
                                                        <?= $entry['WIN_RATE'] ?>%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Player Leaderboard -->
        <div class="col-lg-5 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user-trophy me-2 text-success"></i>Top Players</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($playerStats)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-user fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No player data available.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($playerStats as $i => $p): ?>
                            <div class="d-flex align-items-center justify-content-between mb-3 p-2 bg-light rounded">
                                <div class="d-flex align-items-center">
                                    <span class="rank-badge <?= $i == 0 ? 'gold' : ($i == 1 ? 'silver' : ($i == 2 ? 'bronze' : '')) ?> me-3">
                                        <?= $i + 1 ?>
                                    </span>
                                    <div>
                                        <h6 class="mb-0"><?= sanitize($p['FULL_NAME']) ?></h6>
                                        <small class="text-muted"><?= sanitize($p['DEPARTMENT'] ?: 'General') ?></small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="text-warning fw-bold"><?= $p['TOTAL_POINTS'] ?> PTS</span>
                                    <br>
                                    <small class="text-muted"><?= $p['TOTAL_MVPS'] ?> MVPs</small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
