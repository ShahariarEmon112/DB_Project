<?php
/**
 * GameArena - Admin Users Page
 */
$pageTitle = 'Manage Users';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';

Auth::requireAdmin();
$db = getDB();

$search = $_GET['search'] ?? '';
$role = $_GET['role'] ?? '';

$sql = "SELECT u.*, r.role_name FROM users u JOIN roles r ON u.role_id = r.role_id WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (UPPER(u.full_name) LIKE :search OR UPPER(u.username) LIKE :search OR UPPER(u.email) LIKE :search)";
    $params[':search'] = '%' . strtoupper($search) . '%';
}
if ($role) {
    $sql .= " AND u.role_id = :role";
    $params[':role'] = $role;
}
$sql .= " ORDER BY u.created_at DESC";

$users = $db->fetchAll($sql, $params);
$roles = $db->fetchAll("SELECT * FROM roles ORDER BY role_id");

// Handle delete
if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    $db->delete("DELETE FROM users WHERE user_id = :id", [':id' => $deleteId]);
    header('Location: /GameArena/admin/users.php?success=deleted');
    exit;
}

require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="d-flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold"><i class="fas fa-users me-2 text-primary"></i>Manage Users</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="fas fa-plus me-1"></i>Add User
            </button>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>Action completed successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control" placeholder="Search users..." value="<?= sanitize($search) ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="role" class="form-select">
                            <option value="">All Roles</option>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= $r['ROLE_ID'] ?>" <?= $role == $r['ROLE_ID'] ? 'selected' : '' ?>><?= $r['ROLE_NAME'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-1"></i>Filter</button>
                        <a href="/GameArena/admin/users.php" class="btn btn-outline-secondary"><i class="fas fa-times me-1"></i>Clear</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Department</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td><?= $u['USER_ID'] ?></td>
                                    <td><strong><?= sanitize($u['FULL_NAME']) ?></strong></td>
                                    <td><?= sanitize($u['USERNAME']) ?></td>
                                    <td><small class="text-muted"><?= sanitize($u['EMAIL'] ?? '') ?: 'N/A' ?></small></td>
                                    <td><?= sanitize($u['DEPARTMENT'] ?: 'N/A') ?></td>
                                    <td><span class="badge bg-primary"><?= $u['ROLE_NAME'] ?></span></td>
                                    <td><span class="badge bg-<?= $u['IS_ACTIVE'] ? 'success' : 'danger' ?>"><?= $u['IS_ACTIVE'] ? 'Active' : 'Inactive' ?></span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary edit-user" data-id="<?= $u['USER_ID'] ?>" data-name="<?= sanitize($u['FULL_NAME']) ?>" data-email="<?= sanitize($u['EMAIL'] ?? '') ?>" data-dept="<?= sanitize($u['DEPARTMENT']) ?>" data-role="<?= $u['ROLE_ID'] ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="?delete=<?= $u['USER_ID'] ?>" class="btn btn-sm btn-outline-danger btn-delete" onclick="return confirm('Delete this user?')">
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

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/GameArena/controllers/user_controller.php">
                <input type="hidden" name="action" value="create">
                <div class="modal-header border-0">
                    <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Add User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username *</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <select name="department" class="form-select">
                            <option value="">Select</option>
                            <option value="CSE">CSE</option>
                            <option value="EEE">EEE</option>
                            <option value="ECE">ECE</option>
                            <option value="ME">ME</option>
                            <option value="CE">CE</option>
                            <option value="URP">URP</option>
                            <option value="BME">BME</option>
                            <option value="IEM">IEM</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Student ID</label>
                        <input type="text" name="student_id" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role_id" class="form-select">
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= $r['ROLE_ID'] ?>"><?= $r['ROLE_NAME'] ?></option>
                            <?php endforeach; ?>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

