<?php
/**
 * GameArena - Admin Tournaments Page
 */
$pageTitle = 'Manage Tournaments';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';

Auth::requireAdmin();
$db = getDB();

$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

$sql = "SELECT t.*, gc.category_name,
            (SELECT COUNT(*) FROM tournament_registrations tr WHERE tr.tournament_id = t.tournament_id AND tr.status = 'Confirmed') AS registered_teams
        FROM tournaments t
        JOIN game_categories gc ON t.category_id = gc.category_id
        WHERE 1=1";
$params = [];

if ($status) {
    $sql .= " AND t.status = :status";
    $params[':status'] = $status;
}
if ($search) {
    $sql .= " AND UPPER(t.tournament_name) LIKE :search";
    $params[':search'] = '%' . strtoupper($search) . '%';
}
$sql .= " ORDER BY t.created_at DESC";

$tournaments = $db->fetchAll($sql, $params);
$categories = $db->fetchAll("SELECT * FROM game_categories WHERE is_active = 1");

// Handle delete
if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    $db->delete("DELETE FROM tournaments WHERE tournament_id = :id", [':id' => $deleteId]);
    header('Location: /GameArena/admin/tournaments.php?success=deleted');
    exit;
}

require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="d-flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold"><i class="fas fa-trophy me-2 text-warning"></i>Manage Tournaments</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTournamentModal">
                <i class="fas fa-plus me-1"></i>Add Tournament
            </button>
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
                        <input type="text" name="search" class="form-control" placeholder="Search..." value="<?= sanitize($search) ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="Upcoming" <?= $status === 'Upcoming' ? 'selected' : '' ?>>Upcoming</option>
                            <option value="Active" <?= $status === 'Active' ? 'selected' : '' ?>>Active</option>
                            <option value="Completed" <?= $status === 'Completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="Cancelled" <?= $status === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-1"></i>Filter</button>
                        <a href="/GameArena/admin/tournaments.php" class="btn btn-outline-secondary">Clear</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Dates</th>
                                <th>Prize</th>
                                <th>Teams</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tournaments as $t): ?>
                                <tr>
                                    <td><?= $t['TOURNAMENT_ID'] ?></td>
                                    <td><strong><?= sanitize($t['TOURNAMENT_NAME']) ?></strong></td>
                                    <td><small><?= sanitize($t['CATEGORY_NAME']) ?></small></td>
                                    <td>
                                        <small><?= date('M d', strtotime($t['START_DATE'])) ?></small>
                                        <?php if ($t['END_DATE']): ?>
                                            <br><small class="text-muted">to <?= date('M d', strtotime($t['END_DATE'])) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-warning"><?= number_format($t['PRIZE_POOL']) ?></td>
                                    <td><?= $t['REGISTERED_TEAMS'] ?>/<?= $t['MAX_TEAMS'] ?></td>
                                    <td>
                                        <span class="badge bg-<?= $t['STATUS'] === 'Active' ? 'success' : ($t['STATUS'] === 'Upcoming' ? 'info' : ($t['STATUS'] === 'Completed' ? 'secondary' : 'danger')) ?>">
                                            <?= $t['STATUS'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary edit-tournament"
                                                data-id="<?= $t['TOURNAMENT_ID'] ?>"
                                                data-name="<?= sanitize($t['TOURNAMENT_NAME']) ?>"
                                                data-category="<?= $t['CATEGORY_ID'] ?>"
                                                data-start="<?= $t['START_DATE'] ?>"
                                                data-end="<?= $t['END_DATE'] ?>"
                                                data-deadline="<?= $t['REGISTRATION_DEADLINE'] ?>"
                                                data-max="<?= $t['MAX_TEAMS'] ?>"
                                                data-min="<?= $t['MIN_TEAMS'] ?>"
                                                data-prize="<?= $t['PRIZE_POOL'] ?>"
                                                data-fee="<?= $t['ENTRY_FEE'] ?>"
                                                data-status="<?= $t['STATUS'] ?>"
                                                data-venue="<?= sanitize($t['VENUE']) ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="?delete=<?= $t['TOURNAMENT_ID'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
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

<!-- Add Tournament Modal -->
<div class="modal fade" id="addTournamentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="/GameArena/controllers/tournament_controller.php">
                <input type="hidden" name="action" value="create">
                <div class="modal-header border-0">
                    <h5 class="modal-title"><i class="fas fa-trophy me-2"></i>Add Tournament</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Tournament Name *</label>
                            <input type="text" name="tournament_name" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Category *</label>
                            <select name="category_id" class="form-select" required>
                                <?php foreach ($categories as $c): ?>
                                    <option value="<?= $c['CATEGORY_ID'] ?>"><?= sanitize($c['CATEGORY_NAME']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Start Date *</label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Registration Deadline *</label>
                            <input type="date" name="registration_deadline" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Max Teams</label>
                            <input type="number" name="max_teams" class="form-control" value="16">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Min Teams</label>
                            <input type="number" name="min_teams" class="form-control" value="4">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Prize Pool (BDT)</label>
                            <input type="number" name="prize_pool" class="form-control" value="0">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Entry Fee (BDT)</label>
                            <input type="number" name="entry_fee" class="form-control" value="0">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Venue</label>
                            <input type="text" name="venue" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="Upcoming">Upcoming</option>
                                <option value="Active">Active</option>
                                <option value="Completed">Completed</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rules</label>
                        <textarea name="rules" class="form-control" rows="2"></textarea>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

