<?php
$pageTitle = 'Tournaments';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

$db = getDB();
$tournamentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($tournamentId):
    $tournament = $db->fetchOne(
        "SELECT t.*, gc.category_name, gc.icon AS category_icon,
                (SELECT COUNT(*) FROM tournament_registrations tr WHERE tr.tournament_id = t.tournament_id AND tr.status = 'Confirmed') AS registered_teams
         FROM tournaments t
         JOIN game_categories gc ON t.category_id = gc.category_id
         WHERE t.tournament_id = :id",
        [':id' => $tournamentId]
    );
    if (!$tournament) { header('Location: /GameArena/pages/tournaments.php'); exit; }

    $registrations = $db->fetchAll(
        "SELECT tr.*, t.team_name, u.full_name AS registered_by_name
         FROM tournament_registrations tr
         JOIN teams t ON tr.team_id = t.team_id
         LEFT JOIN users u ON tr.registered_by = u.user_id
         WHERE tr.tournament_id = :id
         ORDER BY tr.registration_date DESC",
        [':id' => $tournamentId]
    );

    $matches = $db->fetchAll(
        "SELECT m.*, t1.team_name AS team1_name, t2.team_name AS team2_name
         FROM matches m
         JOIN teams t1 ON m.team1_id = t1.team_id
         JOIN teams t2 ON m.team2_id = t2.team_id
         WHERE m.tournament_id = :id
         ORDER BY m.match_date DESC",
        [':id' => $tournamentId]
    );

    require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container-fluid py-4">
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= sanitize($_GET['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= sanitize($_GET['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <div class="mb-3">
        <a href="/GameArena/pages/tournaments.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Tournaments
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas <?= sanitize($tournament['CATEGORY_ICON']) ?> fa-3x text-primary me-3"></i>
                            <div>
                                <h2 class="fw-bold mb-0"><?= sanitize($tournament['TOURNAMENT_NAME']) ?></h2>
                                <small class="text-muted"><?= sanitize($tournament['CATEGORY_NAME']) ?></small>
                            </div>
                        </div>
                        <span class="badge bg-<?= $tournament['STATUS'] === 'Active' ? 'success' : ($tournament['STATUS'] === 'Upcoming' ? 'info' : ($tournament['STATUS'] === 'Completed' ? 'secondary' : 'danger')) ?> fs-6">
                            <?= $tournament['STATUS'] ?>
                        </span>
                    </div>

                    <p class="mb-4"><?= nl2br(sanitize($tournament['DESCRIPTION'])) ?></p>

                    <div class="row text-center g-3 mb-4">
                        <div class="col-md-3 col-6">
                            <div class="bg-warning bg-opacity-10 rounded p-3">
                                <i class="fas fa-coins fa-2x text-warning mb-2"></i>
                                <h5 class="mb-0"><?= number_format($tournament['PRIZE_POOL']) ?></h5>
                                <small class="text-muted">Prize Pool</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="bg-info bg-opacity-10 rounded p-3">
                                <i class="fas fa-users fa-2x text-info mb-2"></i>
                                <h5 class="mb-0"><?= $tournament['REGISTERED_TEAMS'] ?>/<?= $tournament['MAX_TEAMS'] ?></h5>
                                <small class="text-muted">Teams</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="bg-primary bg-opacity-10 rounded p-3">
                                <i class="fas fa-ticket fa-2x text-primary mb-2"></i>
                                <h5 class="mb-0"><?= number_format($tournament['ENTRY_FEE']) ?></h5>
                                <small class="text-muted">Entry Fee</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="bg-success bg-opacity-10 rounded p-3">
                                <i class="fas fa-calendar fa-2x text-success mb-2"></i>
                                <h5 class="mb-0"><?= date('M d', strtotime($tournament['START_DATE'])) ?></h5>
                                <small class="text-muted">Start Date</small>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-1"><i class="fas fa-calendar-alt me-2 text-primary"></i><strong>Start:</strong> <?= date('M d, Y', strtotime($tournament['START_DATE'])) ?></p>
                            <?php if ($tournament['END_DATE']): ?>
                                <p class="mb-1"><i class="fas fa-calendar-check me-2 text-primary"></i><strong>End:</strong> <?= date('M d, Y', strtotime($tournament['END_DATE'])) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <?php if ($tournament['VENUE']): ?>
                                <p class="mb-1"><i class="fas fa-map-marker-alt me-2 text-danger"></i><strong>Venue:</strong> <?= sanitize($tournament['VENUE']) ?></p>
                            <?php endif; ?>
                            <?php if ($tournament['REGISTRATION_DEADLINE']): ?>
                                <p class="mb-1"><i class="fas fa-clock me-2 text-warning"></i><strong>Reg. Deadline:</strong> <?= date('M d, Y', strtotime($tournament['REGISTRATION_DEADLINE'])) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($tournament['RULES']): ?>
                        <div class="mb-3">
                            <h6><i class="fas fa-gavel me-2"></i>Rules</h6>
                            <div class="bg-light rounded p-3"><?= nl2br(sanitize($tournament['RULES'])) ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($matches)): ?>
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0"><i class="fas fa-gamepad me-2"></i>Matches</h5></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead><tr><th>Match</th><th>Teams</th><th>Date</th><th>Status</th><th>Score</th></tr></thead>
                            <tbody>
                                <?php foreach ($matches as $m): ?>
                                <tr>
                                    <td><?= sanitize($m['MATCH_NAME'] ?: 'Match') ?></td>
                                    <td><?= sanitize($m['TEAM1_NAME']) ?> vs <?= sanitize($m['TEAM2_NAME']) ?></td>
                                    <td><small><?= date('M d, Y', strtotime($m['MATCH_DATE'])) ?></small></td>
                                    <td><span class="badge bg-<?= $m['STATUS'] === 'Completed' ? 'secondary' : ($m['STATUS'] === 'Live' ? 'danger' : 'info') ?>"><?= $m['STATUS'] ?></span></td>
                                    <td><?= $m['TEAM1_SCORE'] !== null ? $m['TEAM1_SCORE'] . ' - ' . $m['TEAM2_SCORE'] : '-' ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            <?php if (Auth::isLoggedIn() && $tournament['STATUS'] === 'Upcoming'): ?>
            <div class="card mb-4">
                <div class="card-body text-center">
                    <h5>Register for this Tournament</h5>
                    <p class="text-muted small">Team registration is now open</p>
                    <form method="POST" action="/GameArena/controllers/tournament_controller.php">
                        <input type="hidden" name="action" value="register">
                        <input type="hidden" name="tournament_id" value="<?= $tournament['TOURNAMENT_ID'] ?>">
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-sign-in-alt me-2"></i>Register Now
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0"><i class="fas fa-list me-2"></i>Registered Teams (<?= count($registrations) ?>)</h5></div>
                <div class="card-body">
                    <?php if (empty($registrations)): ?>
                        <p class="text-muted text-center">No teams registered yet</p>
                    <?php else: ?>
                        <?php foreach ($registrations as $i => $reg): ?>
                        <div class="d-flex align-items-center mb-2 p-2 rounded <?= $i % 2 === 0 ? 'bg-light' : '' ?>">
                            <span class="badge bg-primary me-2"><?= $i + 1 ?></span>
                            <div>
                                <strong><?= sanitize($reg['TEAM_NAME']) ?></strong>
                                <br><small class="text-muted">by <?= sanitize($reg['REGISTERED_BY_NAME'] ?: 'N/A') ?></small>
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

<?php else: ?>

<?php
$categories = $db->fetchAll("SELECT * FROM game_categories WHERE is_active = 1 ORDER BY category_name");

$status = $_GET['status'] ?? '';
$category = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';

$sql = "SELECT t.*, gc.category_name, gc.icon AS category_icon,
            (SELECT COUNT(*) FROM tournament_registrations tr WHERE tr.tournament_id = t.tournament_id AND tr.status = 'Confirmed') AS registered_teams
        FROM tournaments t
        JOIN game_categories gc ON t.category_id = gc.category_id
        WHERE 1=1";
$params = [];

if ($status) {
    $sql .= " AND t.status = :status";
    $params[':status'] = $status;
}
if ($category) {
    $sql .= " AND t.category_id = :category";
    $params[':category'] = $category;
}
if ($search) {
    $sql .= " AND UPPER(t.tournament_name) LIKE :search";
    $params[':search'] = '%' . strtoupper($search) . '%';
}
$sql .= " ORDER BY t.start_date DESC";

$tournaments = $db->fetchAll($sql, $params);

require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container-fluid py-4">
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= sanitize($_GET['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= sanitize($_GET['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold"><i class="fas fa-trophy me-2 text-warning"></i>Tournaments</h2>
            <p class="text-muted">Browse and register for gaming tournaments</p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <input type="text" name="search" class="form-control"
                                   placeholder="Search tournaments..."
                                   value="<?= sanitize($search) ?>">
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="Active" <?= $status === 'Active' ? 'selected' : '' ?>>Active</option>
                                <option value="Upcoming" <?= $status === 'Upcoming' ? 'selected' : '' ?>>Upcoming</option>
                                <option value="Completed" <?= $status === 'Completed' ? 'selected' : '' ?>>Completed</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="category" class="form-select">
                                <option value="">All Games</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['CATEGORY_ID'] ?>" <?= $category == $cat['CATEGORY_ID'] ? 'selected' : '' ?>>
                                        <?= sanitize($cat['CATEGORY_NAME']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-filter me-1"></i>Filter
                            </button>
                            <a href="/GameArena/pages/tournaments.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <?php if (empty($tournaments)): ?>
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-trophy fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No tournaments found</h5>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($tournaments as $t): ?>
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card h-100 tournament-card">
                        <div class="card-body">
                            <span class="badge bg-<?= $t['STATUS'] === 'Active' ? 'success' : ($t['STATUS'] === 'Upcoming' ? 'info' : ($t['STATUS'] === 'Completed' ? 'secondary' : 'danger')) ?> tournament-status">
                                <?= $t['STATUS'] ?>
                            </span>
                            <div class="d-flex align-items-center mb-3">
                                <div class="me-3">
                                    <i class="fas <?= $t['CATEGORY_ICON'] ?> fa-2x text-primary"></i>
                                </div>
                                <div>
                                    <h5 class="card-title mb-0"><?= sanitize($t['TOURNAMENT_NAME']) ?></h5>
                                    <small class="text-muted"><?= sanitize($t['CATEGORY_NAME']) ?></small>
                                </div>
                            </div>

                            <p class="card-text small text-muted">
                                <?= strlen($t['DESCRIPTION']) > 100 ? substr($t['DESCRIPTION'], 0, 100) . '...' : sanitize($t['DESCRIPTION']) ?>
                            </p>

                            <div class="row text-center mb-3">
                                <div class="col-4">
                                    <small class="text-muted d-block">Prize Pool</small>
                                    <strong class="text-warning"><?= number_format($t['PRIZE_POOL']) ?></strong>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted d-block">Teams</small>
                                    <strong class="text-info"><?= $t['REGISTERED_TEAMS'] ?>/<?= $t['MAX_TEAMS'] ?></strong>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted d-block">Entry Fee</small>
                                    <strong><?= number_format($t['ENTRY_FEE']) ?></strong>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    <?= date('M d, Y', strtotime($t['START_DATE'])) ?>
                                </small>
                                <?php if ($t['VENUE']): ?>
                                    <small class="text-muted">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        <?= sanitize($t['VENUE']) ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent">
                            <div class="d-flex gap-2">
                                <a href="/GameArena/pages/tournaments.php?id=<?= $t['TOURNAMENT_ID'] ?>"
                                   class="btn btn-sm btn-outline-primary flex-fill">
                                    <i class="fas fa-info-circle me-1"></i>Details
                                </a>
                                <?php if (Auth::isLoggedIn() && $t['STATUS'] === 'Upcoming'): ?>
                                    <form method="POST" action="/GameArena/controllers/tournament_controller.php" class="d-inline flex-fill">
                                        <input type="hidden" name="action" value="register">
                                        <input type="hidden" name="tournament_id" value="<?= $t['TOURNAMENT_ID'] ?>">
                                        <button type="submit" class="btn btn-sm btn-primary w-100">
                                            <i class="fas fa-sign-in-alt me-1"></i>Register
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
<?php endif; ?>
