<?php
// All dashboard pages share the same sidebar.
function renderDashboardSidebar(string $activePage): void
{
    $role = $_SESSION['role'] ?? '';

    // Find the dashboard folder from the current URL (no fixed /artcomsystem path).
    $folder = dirname($_SERVER['SCRIPT_NAME']);
    $siteFolder = preg_replace('~/dashboard(?:/.*)?$~', '', $folder);
    $dashboard = rtrim($siteFolder, '/') . '/dashboard/';
    ?>
    <aside class="sidebar">
        <div>
            <div class="sidebar-header"><div class="brand">Pawcoms</div><div class=><?php echo $_SESSION['role'] ?></div></div>
            <ul class="sidebar-menu">
                <?php if ($role === 'admin'): ?>
                    <li class="<?php echo $activePage === 'dashboard' ? 'active' : ''; ?>">
                        <a href="<?php echo $dashboard; ?>admin_dashboard.php">Dashboard</a>
                    </li>
                    <li class="<?php echo $activePage === 'users' ? 'active' : ''; ?>">
                        <a href="<?php echo $dashboard; ?>users.php">Users</a>
                    </li>
                <?php endif; ?>

                <li class="<?php echo $activePage === 'artwork_feed' ? 'active' : ''; ?>">
                    <a href="<?php echo $dashboard; ?>artwork_feed.php">Artwork Feed</a>
                </li>
                <li class="<?php echo $activePage === 'artists' ? 'active' : ''; ?>">
                    <a href="<?php echo $dashboard; ?>artists.php">Artists</a>
                </li>
                <li class="<?php echo $activePage === 'pinned_artworks' ? 'active' : ''; ?>">
                    <a href="<?php echo $dashboard; ?>pinned_artworks.php">Pinned Artworks</a>
                </li>
                <li class="<?php echo $activePage === 'profile' ? 'active' : ''; ?>">
                    <a href="<?php echo $dashboard; ?>profile.php">Profile</a>
                </li>
                <?php if ($role !== 'commissioner'): ?>
                    <li class="<?php echo $activePage === 'upload' ? 'active' : ''; ?>">
                        <a href="<?php echo $dashboard; ?>upload.php">Upload Image</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
        <div class="sidebar-footer">
            <a href="<?php echo $dashboard; ?>logout.php" class="btn-logout"
               onclick="return confirm('Are you sure you want to log out?');">Logout</a>
        </div>
    </aside>
    <?php
}
