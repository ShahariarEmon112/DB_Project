<?php
/**
 * GameArena - Reports Page
 */
$pageTitle = 'Reports';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

$db = getDB();

// Overall stats
$totalPlayers = (int)$db->fetchColumn("SELECT COUNT(*) FROM users WHERE role_id != 1");
$totalTeams = (int)$db->fetchColumn("SELECT COUNT(*) FROM teams WHERE is_active = 1");
$totalTournaments = (int)$db->fetchColumn("SELECT COUNT(*) FROM tournaments");
$totalMatches = (int)$db->fetchColumn("SELECT COUNT(*) FROM matches");
$completedMatches = (int)$db->fetchColumn("SELECT COUNT(*) FROM matches WHERE status = 'Completed'");

// Top teams
$topTeams = $db->fetchAll(
    "SELECT t.team_name, t.department,
            NVL(GET_TEAM_WIN_RATE(t.team_id), 0) AS win_rate,
            GET_TOTAL_MATCHES(t.team_id) AS total_matches
     FROM teams t
     WHERE t.is_active = 1
     ORDER BY win_rate DESC
     FETCH FIRST 5 ROWS ONLY"
);

// Top players
$topPlayers = $db->fetchAll(
    "SELECT u.full_name, u.department,
            NVL(SUM(ps.points), 0) AS total_points,
            NVL(SUM(ps.mvp_count), 0) AS total_mvps
     FROM users u
     LEFT JOIN player_statistics ps ON u.user_id = ps.player_id
     WHERE u.role_id != 1
     GROUP BY u.user_id, u.full_name, u.department
     ORDER BY total_points DESC
     FETCH FIRST 5 ROWS ONLY"
);

// Tournament stats
$tournamentStats = $db->fetchAll(
    "SELECT t.tournament_name, t.status, t.prize_pool,
            (SELECT COUNT(*) FROM tournament_registrations tr WHERE tr.tournament_id = t.tournament_id AND tr.status = 'Confirmed') AS teams,
            (SELECT COUNT(*) FROM matches m WHERE m.tournament_id = t.tournament_id) AS matches
     FROM tournaments t
     ORDER BY t.created_at DESC
     FETCH FIRST 5 ROWS ONLY"
);

// Department stats
$departmentStats = $db->fetchAll(
    "SELECT department, COUNT(*) AS team_count
     FROM teams
     WHERE department IS NOT NULL AND is_active = 1
     GROUP BY department
     ORDER BY team_count DESC"
);

require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold"><i class="fas fa-chart-bar me-2 text-info"></i>Reports</h2>
            <p class="text-muted">Analytics and statistics overview</p>
        </div>
    </div>

    <!-- Overall Stats -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card stat-card blue h-100">
                <div class="card-body text-center">
                    <h3 class="stat-value text-primary"><?= $totalPlayers ?></h3>
                    <small class="text-muted">Players</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card stat-card green h-100">
                <div class="card-body text-center">
                    <h3 class="stat-value text-success"><?= $totalTeams ?></h3>
                    <small class="text-muted">Teams</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card stat-card orange h-100">
                <div class="card-body text-center">
                    <h3 class="stat-value text-warning"><?= $totalTournaments ?></h3>
                    <small class="text-muted">Tournaments</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card stat-card red h-100">
                <div class="card-body text-center">
                    <h3 class="stat-value text-danger"><?= $totalMatches ?></h3>
                    <small class="text-muted">Matches</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card stat-card cyan h-100">
                <div class="card-body text-center">
                    <h3 class="stat-value text-info"><?= $completedMatches ?></h3>
                    <small class="text-muted">Completed</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card stat-card purple h-100">
                <div class="card-body text-center">
                    <h3 class="stat-value" style="color: #6f42c1;"><?= $totalMatches > 0 ? round(($completedMatches / $totalMatches) * 100) : 0 ?>%</h3>
                    <small class="text-muted">Completion</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Top Teams -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-trophy me-2 text-warning"></i>Top Teams (by Win Rate)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Team</th>
                                    <th>Department</th>
                                    <th>Win Rate</th>
                                    <th>Matches</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topTeams as $i => $t): ?>
                                    <tr>
                                        <td><span class="rank-badge <?= $i == 0 ? 'gold' : ($i == 1 ? 'silver' : ($i == 2 ? 'bronze' : '')) ?>"><?= $i + 1 ?></span></td>
                                        <td><strong><?= sanitize($t['TEAM_NAME']) ?></strong></td>
                                        <td><small class="text-muted"><?= sanitize($t['DEPARTMENT'] ?: 'General') ?></small></td>
                                        <td>
                                            <div class="progress" style="height: 20px; width: 100px;">
                                                <div class="progress-bar bg-success" style="width: <?= $t['WIN_RATE'] ?>%">
                                                    <?= $t['WIN_RATE'] ?>%
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= $t['TOTAL_MATCHES'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Players -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-star me-2 text-primary"></i>Top Players (by Points)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Player</th>
                                    <th>Department</th>
                                    <th>Points</th>
                                    <th>MVPs</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topPlayers as $i => $p): ?>
                                    <tr>
                                        <td><span class="rank-badge <?= $i == 0 ? 'gold' : ($i == 1 ? 'silver' : ($i == 2 ? 'bronze' : '')) ?>"><?= $i + 1 ?></span></td>
                                        <td><strong><?= sanitize($p['FULL_NAME']) ?></strong></td>
                                        <td><small class="text-muted"><?= sanitize($p['DEPARTMENT'] ?: 'General') ?></small></td>
                                        <td><span class="text-warning fw-bold"><?= $p['TOTAL_POINTS'] ?></span></td>
                                        <td><i class="fas fa-star text-warning"></i> <?= $p['TOTAL_MVPS'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Tournament Stats -->
        <div class="col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2 text-success"></i>Tournament Overview</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tournament</th>
                                    <th>Status</th>
                                    <th>Prize Pool</th>
                                    <th>Teams</th>
                                    <th>Matches</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tournamentStats as $ts): ?>
                                    <tr>
                                        <td><strong><?= sanitize($ts['TOURNAMENT_NAME']) ?></strong></td>
                                        <td>
                                            <span class="badge bg-<?= $ts['STATUS'] === 'Active' ? 'success' : ($ts['STATUS'] === 'Upcoming' ? 'info' : 'secondary') ?>">
                                                <?= $ts['STATUS'] ?>
                                            </span>
                                        </td>
                                        <td class="text-warning"><?= number_format($ts['PRIZE_POOL']) ?> BDT</td>
                                        <td><?= $ts['TEAMS'] ?></td>
                                        <td><?= $ts['MATCHES'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Department Stats -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-building me-2 text-info"></i>Teams by Department</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($departmentStats as $ds): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span><?= sanitize($ds['DEPARTMENT']) ?></span>
                            <span class="badge bg-primary"><?= $ds['TEAM_COUNT'] ?> teams</span>
                        </div>
                        <div class="progress mb-3" style="height: 10px;">
                            <div class="progress-bar bg-primary" style="width: <?= ($ds['TEAM_COUNT'] / $totalTeams) * 100 ?>%"></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
