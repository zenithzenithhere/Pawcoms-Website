<?php

function renderDashboardNavigation(string $activePage): void
{
    $isAdmin = ($_SESSION['role'] ?? '') === 'admin';
    $isArtist = ($_SESSION['role'] ?? '') === 'artist';
    $isCommissioner = ($_SESSION['role'] ?? '') === 'commissioner';
    ?>
    <nav class="dashboard-nav" aria-label="Dashboard navigation">

        <?php if ($isAdmin): ?>
        <a href="/artcomsystem/dashboard/admin_dashboard.php" <?php echo $activePage === 'dashboard' ? 'aria-current="page"' : ''; ?>>
            Dashboard
        </a>

            <a href="/artcomsystem/dashboard/users.php" <?php echo $activePage === 'users' ? 'aria-current="page"' : ''; ?>>
                Users
            </a>
            
        <?php endif; ?>
        
        
        <a href="/artcomsystem/dashboard/user_dashboard.php" <?php echo $activePage === 'artwork_feed' ? 'aria-current="page"' : ''; ?>>
            Artwork Feed
        </a>
        

        <a href="/artcomsystem/dashboard/artists.php" <?php echo $activePage === 'artists' ? 'aria-current="page"' : ''; ?>>
            Artists
        </a>
        <a href="/artcomsystem/dashboard/profiles.php" <?php echo $activePage === 'profiles' ? 'aria-current="page"' : ''; ?>>
            Pinned Artworks
        </a>
        <a href="/artcomsystem/dashboard/profile.php" <?php echo $activePage === 'profile' ? 'aria-current="page"' : ''; ?>>
            Profile
        </a>
        <?php if (!$isCommissioner): ?>
        <a href="/artcomsystem/dashboard/upload.php" <?php echo $activePage === 'upload' ? 'aria-current="page"' : ''; ?>>
            Upload
        </a>
        <?php endif; ?>
        <a href="/artcomsystem/dashboard/logout.php">Logout</a>
    </nav>
    <?php
}
