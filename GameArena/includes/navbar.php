<?php
/**
 * GameArena - Navbar
 */
$currentUser = Auth::getUser();
?>
<nav class="navbar navbar-expand-lg navbar-dark sticky-top shadow">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="/GameArena/">
            <i class="fas fa-gamepad text-primary me-2"></i>
            <span class="text-primary">Game</span>Arena
            <small class="text-muted ms-1">| KUET</small>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>"
                       href="/GameArena/pages/dashboard.php">
                        <i class="fas fa-home me-1"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'tournaments.php' ? 'active' : '' ?>"
                       href="/GameArena/pages/tournaments.php">
                        <i class="fas fa-trophy me-1"></i>Tournaments
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'teams.php' ? 'active' : '' ?>"
                       href="/GameArena/pages/teams.php">
                        <i class="fas fa-users me-1"></i>Teams
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'matches.php' ? 'active' : '' ?>"
                       href="/GameArena/pages/matches.php">
                        <i class="fas fa-gamepad me-1"></i>Matches
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'leaderboard.php' ? 'active' : '' ?>"
                       href="/GameArena/pages/leaderboard.php">
                        <i class="fas fa-ranking-star me-1"></i>Leaderboard
                    </a>
                </li>
            </ul>

            <ul class="navbar-nav">
                <?php if ($currentUser): ?>
                    <?php if ($currentUser['role_id'] == 1): ?>
                        <li class="nav-item">
                            <a class="nav-link text-warning" href="/GameArena/admin/dashboard.php">
                                <i class="fas fa-shield-halved me-1"></i>Admin Panel
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i><?= sanitize($currentUser['full_name']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/GameArena/pages/player-profile.php?id=<?= $currentUser['id'] ?>">
                                <i class="fas fa-user me-2"></i>My Profile
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="/GameArena/controllers/auth_controller.php" class="d-inline">
                                    <input type="hidden" name="action" value="logout">
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/GameArena/pages/login.php">
                            <i class="fas fa-sign-in-alt me-1"></i>Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/GameArena/pages/register.php">
                            <i class="fas fa-user-plus me-1"></i>Register
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
