<?php
/**
 * GameArena - Admin Dashboard
 */
$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';

Auth::requireAdmin();
$db = getDB();

// Stats
$stats = [
    'players' => (int)$db->fetchColumn("SELECT COUNT(*) FROM users WHERE role_id != 1"),
    'teams' => (int)$db->fetchColumn("SELECT COUNT(*) FROM teams WHERE is_active = 1"),
    'tournaments' => (int)$db->fetchColumn("SELECT COUNT(*) FROM tournaments"),
    'active_tournaments' => (int)$db->fetchColumn("SELECT COUNT(*) FROM tournaments WHERE status = 'Active'"),
    'matches' => (int)$db->fetchColumn("SELECT COUNT(*) FROM matches"),
    'completed_matches' => (int)$db->fetchColumn("SELECT COUNT(*) FROM matches WHERE status = 'Completed'"),
    'total_prize' => (float)$db->fetchColumn("SELECT NVL(SUM(prize_pool), 0) FROM tournaments")
];

// Recent users
$recentUsers = $db->fetchAll(
    "SELECT u.*, r.role_name FROM users u JOIN roles r ON u.role_id = r.role_id ORDER BY u.created_at DESC FETCH FIRST 5 ROWS ONLY"
);

// Recent activity
$recentActivity = $db->fetchAll(
    "SELECT * FROM audit_log ORDER BY performed_at DESC FETCH FIRST 10 ROWS ONLY"
);

require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="d-flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="flex-grow-1 p-4">
        <h2 class="fw-bold mb-4"><i class="fas fa-tachometer-alt me-2 text-primary"></i>Admin Dashboard</h2>

        <!-- Stats -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card blue h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Total Players</p>
                                <h3 class="stat-value text-primary"><?= $stats['players'] ?></h3>
                            </div>
                            <div class="stat-icon text-primary"><i class="fas fa-users"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card green h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Total Teams</p>
                                <h3 class="stat-value text-success"><?= $stats['teams'] ?></h3>
                            </div>
                            <div class="stat-icon text-success"><i class="fas fa-shield-halved"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card orange h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Tournaments</p>
                                <h3 class="stat-value text-warning"><?= $stats['tournaments'] ?></h3>
                            </div>
                            <div class="stat-icon text-warning"><i class="fas fa-trophy"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card red h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Matches</p>
                                <h3 class="stat-value text-danger"><?= $stats['matches'] ?></h3>
                            </div>
                            <div class="stat-icon text-danger"><i class="fas fa-gamepad"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Users -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between">
                        <h5 class="mb-0">Recent Users</h5>
                        <a href="/GameArena/admin/users.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Joined</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentUsers as $u): ?>
                                        <tr>
                                            <td><?= sanitize($u['FULL_NAME']) ?></td>
                                            <td><small class="text-muted"><?= sanitize($u['EMAIL'] ?? '') ?: 'N/A' ?></small></td>
                                            <td><span class="badge bg-primary"><?= $u['ROLE_NAME'] ?></span></td>
                                            <td><small><?= date('M d', strtotime($u['CREATED_AT'])) ?></small></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Audit Log -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Activity</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentActivity)): ?>
                            <p class="text-muted text-center">No recent activity.</p>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($recentActivity as $log): ?>
                                    <div class="list-group-item bg-transparent border-0 px-0">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <span class="badge bg-<?= $log['ACTION'] === 'INSERT' ? 'success' : ($log['ACTION'] === 'UPDATE' ? 'warning' : 'danger') ?>">
                                                    <?= $log['ACTION'] ?>
                                                </span>
                                                <strong class="ms-2"><?= sanitize($log['TABLE_NAME']) ?></strong>
                                            </div>
                                            <small class="text-muted"><?= date('M d H:i', strtotime($log['PERFORMED_AT'])) ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
