<?php
/**
 * GameArena - Player Profile Page
 */
$pageTitle = 'Player Profile';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

$db = getDB();

$userId = (int)($_GET['id'] ?? Auth::getUserId());
if (!$userId) {
    header('Location: /GameArena/pages/login.php');
    exit;
}

$user = $db->fetchOne(
    "SELECT u.*, r.role_name FROM users u JOIN roles r ON u.role_id = r.role_id WHERE u.user_id = :id",
    [':id' => $userId]
);

if (!$user) {
    header('Location: /GameArena/pages/dashboard.php');
    exit;
}

// Get player stats
$stats = $db->fetchOne(
    "SELECT NVL(SUM(matches_played), 0) AS total_matches,
            NVL(SUM(wins), 0) AS total_wins,
            NVL(SUM(losses), 0) AS total_losses,
            NVL(SUM(kills), 0) AS total_kills,
            NVL(SUM(deaths), 0) AS total_deaths,
            NVL(SUM(assists), 0) AS total_assists,
            NVL(SUM(mvp_count), 0) AS total_mvps,
            NVL(SUM(points), 0) AS total_points
     FROM player_statistics WHERE player_id = :id",
    [':id' => $userId]
);

$stats['kd_ratio'] = $stats['TOTAL_DEATHS'] > 0
    ? round($stats['TOTAL_KILLS'] / $stats['TOTAL_DEATHS'], 2)
    : $stats['TOTAL_KILLS'];

// Get team
$team = $db->fetchOne(
    "SELECT t.* FROM teams t JOIN team_members tm ON t.team_id = tm.team_id WHERE tm.user_id = :id",
    [':id' => $userId]
);

// Get match history
$matchHistory = $db->fetchAll(
    "SELECT m.*, t1.team_name AS team1_name, t2.team_name AS team2_name,
            trn.tournament_name, mr.team1_score, mr.team2_score,
            tw.team_name AS winner_name
     FROM matches m
     JOIN teams t1 ON m.team1_id = t1.team_id
     JOIN teams t2 ON m.team2_id = t2.team_id
     JOIN tournaments trn ON m.tournament_id = trn.tournament_id
     LEFT JOIN match_results mr ON m.match_id = mr.match_id
     LEFT JOIN teams tw ON mr.winner_id = tw.team_id
     WHERE (m.team1_id IN (SELECT team_id FROM team_members WHERE user_id = :id)
            OR m.team2_id IN (SELECT team_id FROM team_members WHERE user_id = :id))
       AND m.status = 'Completed'
     ORDER BY m.match_date DESC
     FETCH FIRST 10 ROWS ONLY",
    [':id' => $userId]
);

require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <!-- Profile Card -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-center mx-auto" style="width: 100px; height: 100px; border-radius: 50%; border: 3px solid var(--primary); background: linear-gradient(135deg, #ede9fe, #c4b5fd);">
                            <i class="fas fa-user fa-3x" style="color: var(--primary)"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold"><?= sanitize($user['FULL_NAME']) ?></h4>
                    <p class="text-muted">@<?= sanitize($user['USERNAME']) ?></p>

                    <div class="row text-center mb-3">
                        <div class="col-4">
                            <small class="text-muted d-block">Department</small>
                            <strong><?= sanitize($user['DEPARTMENT'] ?: 'N/A') ?></strong>
                        </div>
                        <div class="col-4">
                            <small class="text-muted d-block">Student ID</small>
                            <strong><?= sanitize($user['STUDENT_ID'] ?: 'N/A') ?></strong>
                        </div>
                        <div class="col-4">
                            <small class="text-muted d-block">Role</small>
                            <span class="badge bg-primary"><?= $user['ROLE_NAME'] ?></span>
                        </div>
                    </div>

                    <?php if ($team): ?>
                        <div class="bg-light rounded p-3">
                            <small class="text-muted d-block mb-1">Team</small>
                            <strong class="text-primary">
                                <i class="fas fa-shield-halved me-1"></i>
                                <?= sanitize($team['TEAM_NAME']) ?>
                            </strong>
                        </div>
                    <?php endif; ?>

                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="fas fa-calendar me-1"></i>
                            Joined: <?= date('M d, Y', strtotime($user['CREATED_AT'])) ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Card -->
        <div class="col-lg-8">
            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-3 col-6 mb-3">
                    <div class="card stat-card blue h-100">
                        <div class="card-body text-center">
                            <h3 class="stat-value text-primary"><?= $stats['TOTAL_MATCHES'] ?></h3>
                            <small class="text-muted">Matches</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <div class="card stat-card green h-100">
                        <div class="card-body text-center">
                            <h3 class="stat-value text-success"><?= $stats['TOTAL_WINS'] ?></h3>
                            <small class="text-muted">Wins</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <div class="card stat-card orange h-100">
                        <div class="card-body text-center">
                            <h3 class="stat-value text-warning"><?= $stats['TOTAL_KILLS'] ?></h3>
                            <small class="text-muted">Kills</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <div class="card stat-card red h-100">
                        <div class="card-body text-center">
                            <h3 class="stat-value text-danger"><?= $stats['TOTAL_MVPS'] ?></h3>
                            <small class="text-muted">MVPs</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Stats -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Detailed Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr><td class="text-muted">Total Points</td><td class="fw-bold text-warning"><?= $stats['TOTAL_POINTS'] ?></td></tr>
                                <tr><td class="text-muted">K/D Ratio</td><td class="fw-bold"><?= $stats['kd_ratio'] ?></td></tr>
                                <tr><td class="text-muted">Kills</td><td class="fw-bold text-success"><?= $stats['TOTAL_KILLS'] ?></td></tr>
                                <tr><td class="text-muted">Deaths</td><td class="fw-bold text-danger"><?= $stats['TOTAL_DEATHS'] ?></td></tr>
                                <tr><td class="text-muted">Assists</td><td class="fw-bold text-info"><?= $stats['TOTAL_ASSISTS'] ?></td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr><td class="text-muted">Win Rate</td><td class="fw-bold text-success"><?= $stats['TOTAL_MATCHES'] > 0 ? round(($stats['TOTAL_WINS'] / $stats['TOTAL_MATCHES']) * 100, 1) : 0 ?>%</td></tr>
                                <tr><td class="text-muted">Wins</td><td class="fw-bold text-success"><?= $stats['TOTAL_WINS'] ?></td></tr>
                                <tr><td class="text-muted">Losses</td><td class="fw-bold text-danger"><?= $stats['TOTAL_LOSSES'] ?></td></tr>
                                <tr><td class="text-muted">MVP Awards</td><td class="fw-bold text-warning"><?= $stats['TOTAL_MVPS'] ?></td></tr>
                                <tr><td class="text-muted">Avg. Points/Match</td><td class="fw-bold"><?= $stats['TOTAL_MATCHES'] > 0 ? round($stats['TOTAL_POINTS'] / $stats['TOTAL_MATCHES'], 1) : 0 ?></td></tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Match History -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Match History</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($matchHistory)): ?>
                        <p class="text-muted text-center">No match history available.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Match</th>
                                        <th>Tournament</th>
                                        <th>Teams</th>
                                        <th>Score</th>
                                        <th>Result</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($matchHistory as $mh): ?>
                                        <tr>
                                            <td><?= sanitize($mh['MATCH_NAME'] ?: 'Match') ?></td>
                                            <td><small><?= sanitize($mh['TOURNAMENT_NAME']) ?></small></td>
                                            <td>
                                                <small>
                                                    <?= sanitize($mh['TEAM1_NAME']) ?> vs <?= sanitize($mh['TEAM2_NAME']) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <strong><?= $mh['TEAM1_SCORE'] ?> - <?= $mh['TEAM2_SCORE'] ?></strong>
                                            </td>
                                            <td>
                                                <?php
                                                $playerTeam = $db->fetchOne(
                                                    "SELECT team_id FROM team_members WHERE user_id = :id",
                                                    [':id' => $userId]
                                                );
                                                $won = $playerTeam && $mh['WINNER_ID'] == $playerTeam['TEAM_ID'];
                                                ?>
                                                <span class="badge bg-<?= $won ? 'success' : 'danger' ?>">
                                                    <?= $won ? 'WIN' : 'LOSS' ?>
                                                </span>
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
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
