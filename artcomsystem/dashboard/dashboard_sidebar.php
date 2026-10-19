<?php

function renderDashboardSidebar(string $activePage): void
{
    $isAdmin = ($_SESSION['role'] ?? '') === 'admin';
    $isArtist = ($_SESSION['role'] ?? '') === 'artist';
    $isCommissioner = ($_SESSION['role'] ?? '') === 'commissioner';
    ?>
    <aside class="sidebar">
        <div>
            <div class="sidebar-header">
                <div class="brand">Pawcoms</div>
            </div>

            <ul class="sidebar-menu">
                

                <?php if ($isAdmin): ?>

                    <li class="<?php echo $activePage === 'dashboard' ? 'active' : ''; ?>">
                    <a href="/artcomsystem/dashboard/admin_dashboard.php">Dashboard</a>
                    </li>

                    <li class="<?php echo $activePage === 'users' ? 'active' : ''; ?>">
                        <a href="/artcomsystem/dashboard/users.php">Users</a>
                    </li>

                    
                <?php endif; ?>

                
                <li class="<?php echo $activePage === 'artwork_feed' ? 'active' : ''; ?>">
                    <a href="/artcomsystem/dashboard/user_dashboard.php">Artwork Feed</a>
                </li>
                


                <li class="<?php echo $activePage === 'artists' ? 'active' : ''; ?>">
                    <a href="/artcomsystem/dashboard/artists.php">Artists</a>
                </li>
                <li class="<?php echo $activePage === 'profiles' ? 'active' : ''; ?>">
                    <a href="/artcomsystem/dashboard/profiles.php">Pinned Artworks</a>
                </li>
                <li class="<?php echo $activePage === 'profile' ? 'active' : ''; ?>">
                    <a href="/artcomsystem/dashboard/profile.php">Profile</a>
                </li>
                <?php if (!$isCommissioner): ?>
                    <li class="<?php echo $activePage === 'upload' ? 'active' : ''; ?>">
                        <a href="/artcomsystem/dashboard/upload.php">Upload Image</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="sidebar-footer">
            <a href="/artcomsystem/dashboard/logout.php" class="btn-logout">Logout</a>
        </div>
    </aside>
    <?php
}
