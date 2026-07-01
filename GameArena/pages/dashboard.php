<?php
/**
 * GameArena - Dashboard Page
 */
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

$db = getDB();

// Get stats
$totalPlayers = (int)$db->fetchColumn("SELECT COUNT(*) FROM users WHERE role_id != 1");
$totalTeams = (int)$db->fetchColumn("SELECT COUNT(*) FROM teams WHERE is_active = 1");
$totalTournaments = (int)$db->fetchColumn("SELECT COUNT(*) FROM tournaments");
$activeTournaments = (int)$db->fetchColumn("SELECT COUNT(*) FROM tournaments WHERE status = 'Active'");

// Get upcoming matches
$upcomingMatches = $db->fetchAll(
    "SELECT m.*, t1.team_name AS team1_name, t2.team_name AS team2_name,
            trn.tournament_name, gc.category_name
     FROM matches m
     JOIN teams t1 ON m.team1_id = t1.team_id
     JOIN teams t2 ON m.team2_id = t2.team_id
     JOIN tournaments trn ON m.tournament_id = trn.tournament_id
     JOIN game_categories gc ON trn.category_id = gc.category_id
     WHERE m.status IN ('Scheduled', 'Live')
     ORDER BY m.match_date ASC
     FETCH FIRST 5 ROWS ONLY"
);

// Get recent tournaments
$recentTournaments = $db->fetchAll(
    "SELECT t.*, gc.category_name, gc.icon AS category_icon
     FROM tournaments t
     JOIN game_categories gc ON t.category_id = gc.category_id
     ORDER BY t.created_at DESC
     FETCH FIRST 4 ROWS ONLY"
);

// Get leaderboard
$leaderboard = $db->fetchAll(
    "SELECT lb.*, t.team_name, t.department
     FROM leaderboard lb
     JOIN teams t ON lb.team_id = t.team_id
     WHERE lb.tournament_id = (
         SELECT tournament_id FROM tournaments WHERE status = 'Active'
         FETCH FIRST 1 ROWS ONLY
     )
     ORDER BY lb.points DESC
     FETCH FIRST 5 ROWS ONLY"
);

require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container-fluid py-4">
    <!-- Hero Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white p-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="display-5 fw-bold mb-2">
                            <i class="fas fa-gamepad me-3"></i>GameArena
                        </h1>
                        <p class="lead mb-0">
                            Welcome to KUET's Gaming Tournament Management System
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="/GameArena/pages/tournaments.php" class="btn btn-light btn-lg me-2">
                            <i class="fas fa-trophy me-2"></i>Tournaments
                        </a>
                        <?php if (!Auth::isLoggedIn()): ?>
                            <a href="/GameArena/pages/register.php" class="btn btn-outline-light btn-lg">
                                <i class="fas fa-user-plus me-2"></i>Join Now
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card blue h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Total Players</p>
                            <h3 class="stat-value text-primary"><?= $totalPlayers ?></h3>
                        </div>
                        <div class="stat-icon text-primary">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card green h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Total Teams</p>
                            <h3 class="stat-value text-success"><?= $totalTeams ?></h3>
                        </div>
                        <div class="stat-icon text-success">
                            <i class="fas fa-shield-halved"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card orange h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Tournaments</p>
                            <h3 class="stat-value text-warning"><?= $totalTournaments ?></h3>
                        </div>
                        <div class="stat-icon text-warning">
                            <i class="fas fa-trophy"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card red h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Active Now</p>
                            <h3 class="stat-value text-danger"><?= $activeTournaments ?></h3>
                        </div>
                        <div class="stat-icon text-danger">
                            <i class="fas fa-fire"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Upcoming Matches -->
        <div class="col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-gamepad me-2 text-primary"></i>Upcoming Matches</h5>
                    <a href="/GameArena/pages/matches.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($upcomingMatches)): ?>
                        <p class="text-muted text-center py-4">No upcoming matches scheduled.</p>
                    <?php else: ?>
                        <?php foreach ($upcomingMatches as $match): ?>
                            <div class="d-flex align-items-center justify-content-between p-3 mb-2 bg-light rounded">
                                <div class="d-flex align-items-center">
                                    <div class="text-center me-3">
                                        <small class="text-muted d-block"><?= date('M d', strtotime($match['MATCH_DATE'])) ?></small>
                                        <small class="text-primary"><?= $match['MATCH_TIME'] ?: 'TBD' ?></small>
                                    </div>
                                    <div>
                                        <h6 class="mb-0"><?= sanitize($match['MATCH_NAME'] ?: 'Match') ?></h6>
                                        <small class="text-muted">
                                            <?= sanitize($match['TOURNAMENT_NAME']) ?> | <?= sanitize($match['CATEGORY_NAME']) ?>
                                        </small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-<?= $match['STATUS'] === 'Live' ? 'danger' : 'info' ?>">
                                        <?= $match['STATUS'] ?>
                                    </span>
                                    <div class="mt-1">
                                        <small class="fw-bold">
                                            <?= sanitize($match['TEAM1_NAME']) ?> vs <?= sanitize($match['TEAM2_NAME']) ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Live Leaderboard -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-ranking-star me-2 text-warning"></i>Leaderboard</h5>
                    <a href="/GameArena/pages/leaderboard.php" class="btn btn-sm btn-outline-primary">Full</a>
                </div>
                <div class="card-body">
                    <?php if (empty($leaderboard)): ?>
                        <p class="text-muted text-center py-4">No active leaderboard.</p>
                    <?php else: ?>
                        <?php foreach ($leaderboard as $i => $entry): ?>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="rank-badge <?= $i == 0 ? 'gold' : ($i == 1 ? 'silver' : ($i == 2 ? 'bronze' : '')) ?> me-3">
                                        <?= $i + 1 ?>
                                    </span>
                                    <div>
                                        <h6 class="mb-0"><?= sanitize($entry['TEAM_NAME']) ?></h6>
                                        <small class="text-muted"><?= sanitize($entry['DEPARTMENT']) ?></small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="text-warning fw-bold"><?= $entry['POINTS'] ?> PTS</span>
                                    <br>
                                    <small class="text-success"><?= $entry['WINS'] ?>W</small>
                                    <small class="text-danger"><?= $entry['LOSSES'] ?>L</small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Tournaments -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-trophy me-2 text-warning"></i>Recent Tournaments</h5>
                    <a href="/GameArena/pages/tournaments.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($recentTournaments as $tournament): ?>
                            <div class="col-xl-3 col-md-6 mb-3">
                                <div class="card h-100 tournament-card">
                                    <div class="card-body">
                                        <span class="badge bg-<?= $tournament['STATUS'] === 'Active' ? 'success' : ($tournament['STATUS'] === 'Upcoming' ? 'info' : ($tournament['STATUS'] === 'Completed' ? 'secondary' : 'danger')) ?> tournament-status">
                                            <?= $tournament['STATUS'] ?>
                                        </span>
                                        <div class="mb-3">
                                            <i class="fas <?= $tournament['CATEGORY_ICON'] ?> fa-2x text-primary"></i>
                                        </div>
                                        <h6 class="card-title"><?= sanitize($tournament['TOURNAMENT_NAME']) ?></h6>
                                        <p class="card-text small text-muted">
                                            <?= sanitize($tournament['CATEGORY_NAME']) ?>
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i>
                                                <?= date('M d', strtotime($tournament['START_DATE'])) ?>
                                            </small>
                                            <span class="tournament-prize">
                                                <?= number_format($tournament['PRIZE_POOL']) ?> BDT
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-transparent">
                                        <a href="/GameArena/pages/tournaments.php?id=<?= $tournament['TOURNAMENT_ID'] ?>"
                                           class="btn btn-sm btn-outline-primary w-100">View Details</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
