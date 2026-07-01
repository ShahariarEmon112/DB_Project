<?php
/**
 * GameArena - Admin Matches Page
 */
$pageTitle = 'Manage Matches';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';

Auth::requireAdmin();
$db = getDB();

$status = $_GET['status'] ?? '';

$sql = "SELECT m.*, t1.team_name AS team1_name, t2.team_name AS team2_name,
            trn.tournament_name, mr.team1_score, mr.team2_score
        FROM matches m
        JOIN teams t1 ON m.team1_id = t1.team_id
        JOIN teams t2 ON m.team2_id = t2.team_id
        JOIN tournaments trn ON m.tournament_id = trn.tournament_id
        LEFT JOIN match_results mr ON m.match_id = mr.match_id
        WHERE 1=1";
$params = [];

if ($status) {
    $sql .= " AND m.status = :status";
    $params[':status'] = $status;
}
$sql .= " ORDER BY m.match_date DESC";

$matches = $db->fetchAll($sql, $params);
$tournaments = $db->fetchAll("SELECT tournament_id, tournament_name FROM tournaments ORDER BY tournament_name");
$teams = $db->fetchAll("SELECT team_id, team_name FROM teams WHERE is_active = 1 ORDER BY team_name");

// Handle delete
if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    $db->delete("DELETE FROM matches WHERE match_id = :id", [':id' => $deleteId]);
    header('Location: /GameArena/admin/matches.php?success=deleted');
    exit;
}

require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="d-flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold"><i class="fas fa-gamepad me-2 text-danger"></i>Manage Matches</h2>
            <div class="d-flex gap-2">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMatchModal">
                    <i class="fas fa-plus me-1"></i>Add Match
                </button>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#resultModal">
                    <i class="fas fa-trophy me-1"></i>Record Result
                </button>
            </div>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>Action completed.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="Scheduled" <?= $status === 'Scheduled' ? 'selected' : '' ?>>Scheduled</option>
                            <option value="Live" <?= $status === 'Live' ? 'selected' : '' ?>>Live</option>
                            <option value="Completed" <?= $status === 'Completed' ? 'selected' : '' ?>>Completed</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-1"></i>Filter</button>
                        <a href="/GameArena/admin/matches.php" class="btn btn-outline-secondary">Clear</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Matches Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Match</th>
                                <th>Tournament</th>
                                <th>Teams</th>
                                <th>Date</th>
                                <th>Score</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($matches as $m): ?>
                                <tr>
                                    <td><?= $m['MATCH_ID'] ?></td>
                                    <td><strong><?= sanitize($m['MATCH_NAME'] ?: 'Match') ?></strong><br><small class="text-muted"><?= sanitize($m['ROUND'] ?: 'N/A') ?></small></td>
                                    <td><small><?= sanitize($m['TOURNAMENT_NAME']) ?></small></td>
                                    <td><?= sanitize($m['TEAM1_NAME']) ?> vs <?= sanitize($m['TEAM2_NAME']) ?></td>
                                    <td><small><?= date('M d', strtotime($m['MATCH_DATE'])) ?></small></td>
                                    <td>
                                        <?php if ($m['STATUS'] === 'Completed'): ?>
                                            <strong><?= $m['TEAM1_SCORE'] ?> - <?= $m['TEAM2_SCORE'] ?></strong>
                                        <?php else: ?>-<?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-<?= $m['STATUS'] === 'Completed' ? 'success' : ($m['STATUS'] === 'Live' ? 'danger' : 'info') ?>"><?= $m['STATUS'] ?></span></td>
                                    <td>
                                        <a href="?delete=<?= $m['MATCH_ID'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Match Modal -->
<div class="modal fade" id="addMatchModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/GameArena/controllers/match_controller.php">
                <input type="hidden" name="action" value="create">
                <div class="modal-header border-0">
                    <h5 class="modal-title"><i class="fas fa-gamepad me-2"></i>Add Match</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Match Name *</label>
                        <input type="text" name="match_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tournament *</label>
                        <select name="tournament_id" class="form-select" required>
                            <?php foreach ($tournaments as $t): ?>
                                <option value="<?= $t['TOURNAMENT_ID'] ?>"><?= sanitize($t['TOURNAMENT_NAME']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Team 1 *</label>
                            <select name="team1_id" class="form-select" required>
                                <?php foreach ($teams as $t): ?>
                                    <option value="<?= $t['TEAM_ID'] ?>"><?= sanitize($t['TEAM_NAME']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Team 2 *</label>
                            <select name="team2_id" class="form-select" required>
                                <?php foreach ($teams as $t): ?>
                                    <option value="<?= $t['TEAM_ID'] ?>"><?= sanitize($t['TEAM_NAME']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date *</label>
                            <input type="date" name="match_date" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Time</label>
                            <input type="time" name="match_time" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Venue</label>
                        <input type="text" name="venue" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Round</label>
                        <select name="round" class="form-select">
                            <option value="Group Stage">Group Stage</option>
                            <option value="Quarterfinal">Quarterfinal</option>
                            <option value="Semifinal">Semifinal</option>
                            <option value="Final">Final</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Record Result Modal -->
<div class="modal fade" id="resultModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/GameArena/controllers/match_controller.php">
                <input type="hidden" name="action" value="update_result">
                <div class="modal-header border-0">
                    <h5 class="modal-title"><i class="fas fa-trophy me-2"></i>Record Match Result</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Match *</label>
                        <select name="match_id" class="form-select" required>
                            <?php foreach ($matches as $m): ?>
                                <?php if ($m['STATUS'] !== 'Completed'): ?>
                                    <option value="<?= $m['MATCH_ID'] ?>"><?= sanitize($m['MATCH_NAME'] ?: 'Match') ?>: <?= sanitize($m['TEAM1_NAME']) ?> vs <?= sanitize($m['TEAM2_NAME']) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Team 1 Score *</label>
                            <input type="number" name="team1_score" class="form-control" required min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Team 2 Score *</label>
                            <input type="number" name="team2_score" class="form-control" required min="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Winner *</label>
                        <select name="winner_id" class="form-select" required>
                            <?php foreach ($teams as $t): ?>
                                <option value="<?= $t['TEAM_ID'] ?>"><?= sanitize($t['TEAM_NAME']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Duration (minutes)</label>
                        <input type="number" name="duration_mins" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i>Save Result</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

