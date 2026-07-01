<?php
/**
 * GameArena - Admin Reports Page
 */
$pageTitle = 'Admin Reports';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';

Auth::requireAdmin();
$db = getDB();

// Overall stats
$stats = [
    'total_users' => (int)$db->fetchColumn("SELECT COUNT(*) FROM users"),
    'total_players' => (int)$db->fetchColumn("SELECT COUNT(*) FROM users WHERE role_id = 2"),
    'total_teams' => (int)$db->fetchColumn("SELECT COUNT(*) FROM teams WHERE is_active = 1"),
    'total_tournaments' => (int)$db->fetchColumn("SELECT COUNT(*) FROM tournaments"),
    'active_tournaments' => (int)$db->fetchColumn("SELECT COUNT(*) FROM tournaments WHERE status = 'Active'"),
    'completed_tournaments' => (int)$db->fetchColumn("SELECT COUNT(*) FROM tournaments WHERE status = 'Completed'"),
    'total_matches' => (int)$db->fetchColumn("SELECT COUNT(*) FROM matches"),
    'completed_matches' => (int)$db->fetchColumn("SELECT COUNT(*) FROM matches WHERE status = 'Completed'"),
    'total_prize' => (float)$db->fetchColumn("SELECT NVL(SUM(prize_pool), 0) FROM tournaments"),
    'total_registration' => (int)$db->fetchColumn("SELECT COUNT(*) FROM tournament_registrations")
];

// Audit log
$auditLog = $db->fetchAll(
    "SELECT al.*, u.full_name AS performed_by_name
     FROM audit_log al
     LEFT JOIN users u ON al.performed_by = u.user_id
     ORDER BY al.performed_at DESC
     FETCH FIRST 20 ROWS ONLY"
);

// Tournament performance
$tournamentPerf = $db->fetchAll(
    "SELECT t.tournament_name, t.status, t.prize_pool,
            (SELECT COUNT(*) FROM tournament_registrations tr WHERE tr.tournament_id = t.tournament_id) AS registrations,
            (SELECT COUNT(*) FROM matches m WHERE m.tournament_id = t.tournament_id) AS matches,
            (SELECT COUNT(*) FROM matches m WHERE m.tournament_id = t.tournament_id AND m.status = 'Completed') AS completed
     FROM tournaments t
     ORDER BY t.created_at DESC"
);

require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="d-flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="flex-grow-1 p-4">
        <h2 class="fw-bold mb-4"><i class="fas fa-chart-bar me-2 text-info"></i>Admin Reports</h2>

        <!-- Overview Stats -->
        <div class="row mb-4">
            <div class="col-xl-2 col-md-4 col-6 mb-3">
                <div class="card stat-card blue h-100">
                    <div class="card-body text-center">
                        <h3 class="stat-value text-primary"><?= $stats['total_users'] ?></h3>
                        <small class="text-muted">Users</small>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6 mb-3">
                <div class="card stat-card green h-100">
                    <div class="card-body text-center">
                        <h3 class="stat-value text-success"><?= $stats['total_teams'] ?></h3>
                        <small class="text-muted">Teams</small>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6 mb-3">
                <div class="card stat-card orange h-100">
                    <div class="card-body text-center">
                        <h3 class="stat-value text-warning"><?= $stats['total_tournaments'] ?></h3>
                        <small class="text-muted">Tournaments</small>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6 mb-3">
                <div class="card stat-card red h-100">
                    <div class="card-body text-center">
                        <h3 class="stat-value text-danger"><?= $stats['total_matches'] ?></h3>
                        <small class="text-muted">Matches</small>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6 mb-3">
                <div class="card stat-card cyan h-100">
                    <div class="card-body text-center">
                        <h3 class="stat-value text-info"><?= $stats['total_registration'] ?></h3>
                        <small class="text-muted">Registrations</small>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6 mb-3">
                <div class="card stat-card purple h-100">
                    <div class="card-body text-center">
                        <h3 class="stat-value" style="color: #6f42c1;"><?= number_format($stats['total_prize']) ?></h3>
                        <small class="text-muted">Total Prize (BDT)</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Tournament Performance -->
            <div class="col-lg-8 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-trophy me-2 text-warning"></i>Tournament Performance</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tournament</th>
                                        <th>Status</th>
                                        <th>Prize</th>
                                        <th>Registrations</th>
                                        <th>Matches</th>
                                        <th>Completion</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tournamentPerf as $tp): ?>
                                        <tr>
                                            <td><strong><?= sanitize($tp['TOURNAMENT_NAME']) ?></strong></td>
                                            <td><span class="badge bg-<?= $tp['STATUS'] === 'Active' ? 'success' : ($tp['STATUS'] === 'Completed' ? 'secondary' : 'info') ?>"><?= $tp['STATUS'] ?></span></td>
                                            <td class="text-warning"><?= number_format($tp['PRIZE_POOL']) ?></td>
                                            <td><?= $tp['REGISTRATIONS'] ?></td>
                                            <td><?= $tp['MATCHES'] ?></td>
                                            <td>
                                                <?php
                                                $rate = $tp['MATCHES'] > 0 ? round(($tp['COMPLETED'] / $tp['MATCHES']) * 100) : 0;
                                                ?>
                                                <div class="progress" style="height: 20px; width: 100px;">
                                                    <div class="progress-bar bg-<?= $rate == 100 ? 'success' : 'warning' ?>" style="width: <?= $rate ?>%">
                                                        <?= $rate ?>%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Audit Log -->
            <div class="col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Audit Log</h5>
                    </div>
                    <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                        <?php if (empty($auditLog)): ?>
                            <p class="text-muted text-center">No activity logged.</p>
                        <?php else: ?>
                            <?php foreach ($auditLog as $log): ?>
                                <div class="d-flex justify-content-between mb-2 p-2 bg-light rounded">
                                    <div>
                                        <span class="badge bg-<?= $log['ACTION'] === 'INSERT' ? 'success' : ($log['ACTION'] === 'UPDATE' ? 'warning' : 'danger') ?>">
                                            <?= $log['ACTION'] ?>
                                        </span>
                                        <small class="ms-2"><?= sanitize($log['TABLE_NAME']) ?></small>
                                    </div>
                                    <small class="text-muted"><?= date('M d H:i', strtotime($log['PERFORMED_AT'])) ?></small>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
