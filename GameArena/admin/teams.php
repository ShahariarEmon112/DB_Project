<?php
/**
 * GameArena - Admin Teams Page
 */
$pageTitle = 'Manage Teams';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';

Auth::requireAdmin();
$db = getDB();

$search = $_GET['search'] ?? '';
$dept = $_GET['department'] ?? '';

$sql = "SELECT t.*, u.full_name AS captain_name,
            (SELECT COUNT(*) FROM team_members tm WHERE tm.team_id = t.team_id) AS member_count
        FROM teams t
        LEFT JOIN users u ON t.captain_id = u.user_id
        WHERE t.is_active = 1";
$params = [];

if ($search) {
    $sql .= " AND UPPER(t.team_name) LIKE :search";
    $params[':search'] = '%' . strtoupper($search) . '%';
}
if ($dept) {
    $sql .= " AND t.department = :dept";
    $params[':dept'] = $dept;
}
$sql .= " ORDER BY t.team_name";

$teams = $db->fetchAll($sql, $params);

// Handle delete
if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    $db->delete("DELETE FROM teams WHERE team_id = :id", [':id' => $deleteId]);
    header('Location: /GameArena/admin/teams.php?success=deleted');
    exit;
}

require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="d-flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold"><i class="fas fa-shield-halved me-2 text-primary"></i>Manage Teams</h2>
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
                    <div class="col-md-5">
                        <input type="text" name="search" class="form-control" placeholder="Search teams..." value="<?= sanitize($search) ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="department" class="form-select">
                            <option value="">All Departments</option>
                            <?php foreach (['CSE', 'EEE', 'ECE', 'ME', 'CE', 'URP', 'BME', 'IEM'] as $d): ?>
                                <option value="<?= $d ?>" <?= $dept === $d ? 'selected' : '' ?>><?= $d ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-1"></i>Filter</button>
                        <a href="/GameArena/admin/teams.php" class="btn btn-outline-secondary">Clear</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Teams Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Team Name</th>
                                <th>Department</th>
                                <th>Captain</th>
                                <th>Members</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($teams as $t): ?>
                                <tr>
                                    <td><?= $t['TEAM_ID'] ?></td>
                                    <td><strong><?= sanitize($t['TEAM_NAME']) ?></strong></td>
                                    <td><?= sanitize($t['DEPARTMENT'] ?: 'N/A') ?></td>
                                    <td><?= sanitize($t['CAPTAIN_NAME'] ?: 'N/A') ?></td>
                                    <td><span class="badge bg-info"><?= $t['MEMBER_COUNT'] ?></span></td>
                                    <td>
                                        <a href="?delete=<?= $t['TEAM_ID'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete team?')">
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
