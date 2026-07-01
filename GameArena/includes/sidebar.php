<?php
/**
 * GameArena - Sidebar
 * Used for admin panel
 */
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar p-3" style="min-height: 100vh; width: 250px;">
    <div class="text-center mb-4">
        <h5><i class="fas fa-shield-halved me-2"></i>Admin Panel</h5>
    </div>

    <ul class="nav flex-column">
        <li class="nav-item mb-2">
            <a class="nav-link <?= $currentPage == 'dashboard.php' ? 'active' : '' ?>"
               href="/GameArena/admin/dashboard.php">
                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
            </a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link <?= $currentPage == 'users.php' ? 'active' : '' ?>"
               href="/GameArena/admin/users.php">
                <i class="fas fa-users me-2"></i>Users
            </a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link <?= $currentPage == 'tournaments.php' ? 'active' : '' ?>"
               href="/GameArena/admin/tournaments.php">
                <i class="fas fa-trophy me-2"></i>Tournaments
            </a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link <?= $currentPage == 'teams.php' ? 'active' : '' ?>"
               href="/GameArena/admin/teams.php">
                <i class="fas fa-shield-halved me-2"></i>Teams
            </a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link <?= $currentPage == 'matches.php' ? 'active' : '' ?>"
               href="/GameArena/admin/matches.php">
                <i class="fas fa-gamepad me-2"></i>Matches
            </a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link <?= $currentPage == 'reports.php' ? 'active' : '' ?>"
               href="/GameArena/admin/reports.php">
                <i class="fas fa-chart-bar me-2"></i>Reports
            </a>
        </li>
    </ul>

    <hr class="mt-4">

    <ul class="nav flex-column">
        <li class="nav-item mb-2">
            <a class="nav-link" href="/GameArena/pages/dashboard.php">
                <i class="fas fa-arrow-left me-2"></i>Back to Site
            </a>
        </li>
    </ul>
</div>
