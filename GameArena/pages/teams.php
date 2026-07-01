<?php
$pageTitle = 'Teams';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

$db = getDB();

$department = $_GET['department'] ?? '';
$search = $_GET['search'] ?? '';
$userId = Auth::isLoggedIn() ? Auth::getUserId() : null;

$sql = "SELECT t.*, u.full_name AS captain_name,
            (SELECT COUNT(*) FROM team_members tm WHERE tm.team_id = t.team_id) AS member_count,
            NVL(GET_TEAM_WIN_RATE(t.team_id), 0) AS win_rate,
            GET_TOTAL_MATCHES(t.team_id) AS total_matches
        FROM teams t
        LEFT JOIN users u ON t.captain_id = u.user_id
        WHERE t.is_active = 1";
$params = [];

if ($department) {
    $sql .= " AND t.department = :department";
    $params[':department'] = $department;
}
if ($search) {
    $sql .= " AND UPPER(t.team_name) LIKE :search";
    $params[':search'] = '%' . strtoupper($search) . '%';
}
$sql .= " ORDER BY t.team_name";

$teams = $db->fetchAll($sql, $params);
$departments = $db->fetchAll("SELECT DISTINCT department FROM teams WHERE department IS NOT NULL AND is_active = 1 ORDER BY department");

$myTeam = null;
$pendingRequests = [];
if ($userId) {
    $myTeam = $db->fetchOne(
        "SELECT t.*, u.full_name AS captain_name FROM teams t
         JOIN team_members tm ON t.team_id = tm.team_id
         LEFT JOIN users u ON t.captain_id = u.user_id
         WHERE tm.user_id = :p_user",
        [':p_user' => $userId]
    );

    if ($myTeam && $myTeam['CAPTAIN_ID'] == $userId) {
        $pendingRequests = $db->fetchAll(
            "SELECT r.*, u.full_name, u.username, u.department AS user_dept, u.student_id
             FROM team_join_requests r
             JOIN users u ON r.user_id = u.user_id
             WHERE r.team_id = :tid AND r.status = 'Pending'
             ORDER BY r.created_at DESC",
            [':tid' => $myTeam['TEAM_ID']]
        );
    }
}

$myRequests = [];
if ($userId) {
    $myRequests = $db->fetchAll(
        "SELECT r.*, t.team_name FROM team_join_requests r
         JOIN teams t ON r.team_id = t.team_id
         WHERE r.user_id = :p_user ORDER BY r.created_at DESC",
        [':p_user' => $userId]
    );
}

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
            <h2 class="fw-bold"><i class="fas fa-shield-halved me-2 text-primary"></i>Teams</h2>
            <p class="text-muted">Browse teams and send join requests</p>
        </div>
    </div>

    <?php if ($userId && $myTeam): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-1"><i class="fas fa-check-circle text-success me-2"></i>You are in <strong><?= sanitize($myTeam['TEAM_NAME']) ?></strong></h5>
                            <small class="text-muted">Captain: <?= sanitize($myTeam['CAPTAIN_NAME'] ?: 'N/A') ?> | Members: <?= $myTeam['MEMBER_COUNT'] ?></small>
                        </div>
                        <a href="/GameArena/pages/player-profile.php?id=<?= $userId ?>" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-user me-1"></i>My Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($userId && $myTeam && $myTeam['CAPTAIN_ID'] == $userId && !empty($pendingRequests)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-bell me-2"></i>Pending Join Requests (<?= count($pendingRequests) ?>)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Player</th>
                                    <th>Username</th>
                                    <th>Department</th>
                                    <th>Student ID</th>
                                    <th>Message</th>
                                    <th>Requested</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendingRequests as $r): ?>
                                <tr>
                                    <td><strong><?= sanitize($r['FULL_NAME']) ?></strong></td>
                                    <td><?= sanitize($r['USERNAME']) ?></td>
                                    <td><?= sanitize($r['USER_DEPT'] ?: 'N/A') ?></td>
                                    <td><small><?= sanitize($r['STUDENT_ID'] ?: 'N/A') ?></small></td>
                                    <td><small><?= sanitize($r['MESSAGE'] ?: '-') ?></small></td>
                                    <td><small><?= date('M d, Y', strtotime($r['CREATED_AT'])) ?></small></td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <form method="POST" action="/GameArena/controllers/team_controller.php" class="d-inline">
                                                <input type="hidden" name="action" value="approve_request">
                                                <input type="hidden" name="request_id" value="<?= $r['REQUEST_ID'] ?>">
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                            </form>
                                            <form method="POST" action="/GameArena/controllers/team_controller.php" class="d-inline">
                                                <input type="hidden" name="action" value="reject_request">
                                                <input type="hidden" name="request_id" value="<?= $r['REQUEST_ID'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            </form>
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
    </div>
    <?php endif; ?>

    <?php if ($userId && !empty($myRequests)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-paper-plane me-2"></i>My Join Requests</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Team</th><th>Status</th><th>Date</th></tr></thead>
                            <tbody>
                                <?php foreach ($myRequests as $r): ?>
                                <tr>
                                    <td><?= sanitize($r['TEAM_NAME']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $r['STATUS'] === 'Pending' ? 'warning' : ($r['STATUS'] === 'Approved' ? 'success' : 'danger') ?>">
                                            <?= $r['STATUS'] ?>
                                        </span>
                                    </td>
                                    <td><small><?= date('M d, Y', strtotime($r['CREATED_AT'])) ?></small></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" placeholder="Search teams..." value="<?= sanitize($search) ?>">
                        </div>
                        <div class="col-md-4">
                            <select name="department" class="form-select">
                                <option value="">All Departments</option>
                                <?php foreach ($departments as $d): ?>
                                    <option value="<?= sanitize($d['DEPARTMENT']) ?>" <?= $department === $d['DEPARTMENT'] ? 'selected' : '' ?>>
                                        <?= sanitize($d['DEPARTMENT']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-1"></i>Filter</button>
                            <a href="/GameArena/pages/teams.php" class="btn btn-outline-secondary"><i class="fas fa-times me-1"></i>Clear</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <?php if (empty($teams)): ?>
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-shield-halved fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No teams found</h5>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($teams as $t): ?>
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card h-100 team-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="team-logo me-3 d-flex align-items-center justify-content-center" style="width:50px;height:50px;border-radius:50%;background:linear-gradient(135deg,#ede9fe,#c4b5fd);">
                                    <i class="fas fa-shield-halved" style="color:var(--primary)"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0"><?= sanitize($t['TEAM_NAME']) ?></h5>
                                    <small class="text-muted"><?= sanitize($t['DEPARTMENT'] ?: 'General') ?></small>
                                </div>
                            </div>

                            <div class="mb-2">
                                <small class="text-muted"><i class="fas fa-user-tie me-1"></i>Captain: <strong><?= sanitize($t['CAPTAIN_NAME'] ?: 'N/A') ?></strong></small>
                            </div>

                            <div class="row text-center mb-3">
                                <div class="col-4">
                                    <h6 class="mb-0"><?= $t['MEMBER_COUNT'] ?></h6>
                                    <small class="text-muted">Members</small>
                                </div>
                                <div class="col-4">
                                    <h6 class="mb-0 text-success"><?= $t['TOTAL_MATCHES'] ?></h6>
                                    <small class="text-muted">Matches</small>
                                </div>
                                <div class="col-4">
                                    <h6 class="mb-0 text-warning"><?= $t['WIN_RATE'] ?>%</h6>
                                    <small class="text-muted">Win Rate</small>
                                </div>
                            </div>

                            <?php if ($t['DESCRIPTION']): ?>
                                <p class="small text-muted mb-3">
                                    <?= strlen($t['DESCRIPTION']) > 100 ? substr($t['DESCRIPTION'], 0, 100) . '...' : sanitize($t['DESCRIPTION']) ?>
                                </p>
                            <?php endif; ?>

                            <?php
                            $members = $db->fetchAll(
                                "SELECT u.full_name, tm.role_in_team FROM team_members tm JOIN users u ON tm.user_id = u.user_id WHERE tm.team_id = :tid ORDER BY tm.role_in_team",
                                [':tid' => $t['TEAM_ID']]
                            );
                            ?>
                            <?php if (!empty($members)): ?>
                            <div class="mb-3">
                                <small class="text-muted d-block mb-1"><i class="fas fa-users me-1"></i>Members:</small>
                                <?php foreach ($members as $m): ?>
                                    <span class="badge bg-light text-dark me-1 mb-1">
                                        <?= $m['ROLE_IN_TEAM'] === 'Captain' ? '<i class="fas fa-crown text-warning me-1"></i>' : '' ?>
                                        <?= sanitize($m['FULL_NAME']) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>

                            <?php
                            $hasRequest = false;
                            $isAlreadyMember = false;
                            if ($userId) {
                                $isAlreadyMember = $db->fetchOne(
                                    "SELECT 1 FROM team_members WHERE team_id = :tid AND user_id = :p_user",
                                    [':tid' => $t['TEAM_ID'], ':p_user' => $userId]
                                );
                                if (!$isAlreadyMember) {
                                    $hasRequest = $db->fetchOne(
                                        "SELECT 1 FROM team_join_requests WHERE team_id = :tid AND user_id = :p_user AND status = 'Pending'",
                                        [':tid' => $t['TEAM_ID'], ':p_user' => $userId]
                                    );
                                }
                            }
                            ?>

                            <?php if (Auth::isLoggedIn() && !$isAlreadyMember && $t['TEAM_ID'] != ($myTeam['TEAM_ID'] ?? 0)): ?>
                                <?php if ($hasRequest): ?>
                                    <button class="btn btn-sm btn-outline-warning w-100" disabled>
                                        <i class="fas fa-clock me-1"></i>Request Pending
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-primary w-100" data-bs-toggle="modal" data-bs-target="#joinModal<?= $t['TEAM_ID'] ?>">
                                        <i class="fas fa-sign-in-alt me-1"></i>Join Team
                                    </button>

                                    <div class="modal fade" id="joinModal<?= $t['TEAM_ID'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Join <?= sanitize($t['TEAM_NAME']) ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" action="/GameArena/controllers/team_controller.php">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="action" value="join">
                                                        <input type="hidden" name="team_id" value="<?= $t['TEAM_ID'] ?>">
                                                        <div class="mb-3">
                                                            <label class="form-label">Message to Captain (optional)</label>
                                                            <textarea name="message" class="form-control" rows="3" placeholder="Why do you want to join this team?"></textarea>
                                                        </div>
                                                        <p class="text-muted small"><i class="fas fa-info-circle me-1"></i>Your profile info will be shared with the captain for review.</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i>Send Request</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php elseif (!Auth::isLoggedIn()): ?>
                                <a href="/GameArena/pages/login.php" class="btn btn-sm btn-outline-primary w-100">
                                    <i class="fas fa-sign-in-alt me-1"></i>Login to Join
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
