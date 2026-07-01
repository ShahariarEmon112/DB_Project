<?php
/**
 * GameArena - Matches Page
 */
$pageTitle = 'Matches';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

$db = getDB();

$status = $_GET['status'] ?? '';
$tournament = $_GET['tournament'] ?? '';
$search = $_GET['search'] ?? '';

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

if ($status) {
    $sql .= " AND m.status = :status";
    $params[':status'] = $status;
}
if ($tournament) {
    $sql .= " AND m.tournament_id = :tournament";
    $params[':tournament'] = $tournament;
}
if ($search) {
    $sql .= " AND (UPPER(m.match_name) LIKE :search OR UPPER(t1.team_name) LIKE :search OR UPPER(t2.team_name) LIKE :search)";
    $params[':search'] = '%' . strtoupper($search) . '%';
}
$sql .= " ORDER BY m.match_date DESC";

$matches = $db->fetchAll($sql, $params);
$tournaments = $db->fetchAll("SELECT tournament_id, tournament_name FROM tournaments ORDER BY tournament_name");

require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold"><i class="fas fa-gamepad me-2 text-danger"></i>Matches</h2>
            <p class="text-muted">View all tournament matches and results</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <input type="text" name="search" class="form-control"
                                   placeholder="Search matches..."
                                   value="<?= sanitize($search) ?>">
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="Scheduled" <?= $status === 'Scheduled' ? 'selected' : '' ?>>Scheduled</option>
                                <option value="Live" <?= $status === 'Live' ? 'selected' : '' ?>>Live</option>
                                <option value="Completed" <?= $status === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="Cancelled" <?= $status === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="tournament" class="form-select">
                                <option value="">All Tournaments</option>
                                <?php foreach ($tournaments as $t): ?>
                                    <option value="<?= $t['TOURNAMENT_ID'] ?>" <?= $tournament == $t['TOURNAMENT_ID'] ? 'selected' : '' ?>>
                                        <?= sanitize($t['TOURNAMENT_NAME']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-filter me-1"></i>Filter
                            </button>
                            <a href="/GameArena/pages/matches.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Matches Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <?php if (empty($matches)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-gamepad fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No matches found</h5>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Match</th>
                                        <th>Tournament</th>
                                        <th>Teams</th>
                                        <th>Date</th>
                                        <th>Score</th>
                                        <th>Winner</th>
                                        <th>Status</th>
                                        <th>MVP</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($matches as $m): ?>
                                        <tr>
                                            <td>
                                                <strong><?= sanitize($m['MATCH_NAME'] ?: 'Match #' . $m['MATCH_ID']) ?></strong>
                                                <br><small class="text-muted"><?= sanitize($m['ROUND'] ?: 'N/A') ?></small>
                                            </td>
                                            <td>
                                                <small><?= sanitize($m['TOURNAMENT_NAME']) ?></small>
                                                <br><small class="text-muted"><?= sanitize($m['CATEGORY_NAME']) ?></small>
                                            </td>
                                            <td>
                                                <span class="<?= (isset($m['WINNER_ID']) && $m['WINNER_ID'] == $m['TEAM1_ID']) ? 'text-success fw-bold' : '' ?>">
                                                    <?= sanitize($m['TEAM1_NAME']) ?>
                                                </span>
                                                <span class="text-muted">vs</span>
                                                <span class="<?= (isset($m['WINNER_ID']) && $m['WINNER_ID'] == $m['TEAM2_ID']) ? 'text-success fw-bold' : '' ?>">
                                                    <?= sanitize($m['TEAM2_NAME']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?= date('M d', strtotime($m['MATCH_DATE'])) ?>
                                                <br><small class="text-muted"><?= $m['MATCH_TIME'] ?: 'TBD' ?></small>
                                            </td>
                                            <td>
                                                <?php if ($m['STATUS'] === 'Completed'): ?>
                                                    <span class="team-score">
                                                        <?= $m['TEAM1_SCORE'] ?> - <?= $m['TEAM2_SCORE'] ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($m['WINNER_NAME']): ?>
                                                    <span class="badge bg-success"><?= sanitize($m['WINNER_NAME']) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $m['STATUS'] === 'Completed' ? 'success' : ($m['STATUS'] === 'Live' ? 'danger' : ($m['STATUS'] === 'Scheduled' ? 'info' : 'secondary')) ?>">
                                                    <?= $m['STATUS'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($m['MVP_NAME']): ?>
                                                    <small><i class="fas fa-star text-warning me-1"></i><?= sanitize($m['MVP_NAME']) ?></small>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
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
