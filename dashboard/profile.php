<?php
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

session_start();

if (!isset($_SESSION['id'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../config/database.php';
require_once 'dashboard_sidebar.php';

$profile = null;
$errorMessage = '';
$userId = (int) $_SESSION['id'];
$stmt = $conn->prepare(
    'SELECT id, fullname, email, role, photo
     FROM users
     WHERE id = ?
     LIMIT 1'
);

if ($stmt) {
    $stmt->bind_param('i', $userId);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $profile = $result->fetch_assoc();

        if (!$profile) {
            $errorMessage = 'Your profile could not be found.';
        }
    } else {
        error_log('Profile query failed: ' . $stmt->error);
        $errorMessage = 'Your profile could not be loaded.';
    }

    $stmt->close();
} else {
    error_log('Profile query failed: ' . $conn->error);
    $errorMessage = 'Your profile could not be loaded.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="../css/style.css?v=responsive-2">
    
</head>
<body class="with-sidebar">
    <?php renderDashboardSidebar('profile'); ?>

    

    <main class="page">

        <?php if (isset($_SESSION['profile_success'])): ?>

    <div class="alert-success">
        <?php
        echo htmlspecialchars($_SESSION['profile_success']);
        unset($_SESSION['profile_success']);
        ?>
    </div>

    <?php endif; ?>

        <section class="panel">
            <h1>Profile</h1>
            <p class="description">Your account information.</p>

            <?php if ($errorMessage !== ''): ?>
                <p class="message"><?php echo htmlspecialchars($errorMessage); ?></p>
            <?php else: ?>
                <dl class="profile-list">
                    <div>
                        <?php
                        $profileImage = !empty($profile['photo'])
                            ? '../profile-photos/' . $profile['photo']
                            : '../images/default-profile.png';
                        ?>

                        <div class="profile-picture-container">
                            <img src="<?php echo ($profileImage); ?>" 
                                alt="Profile Picture"
                                class="profile-picture"
                                width="150"
                                height="150">
                        </div>
                    </div>
                    <div>
                        <dt>User ID</dt>
                        <dd><?php echo htmlspecialchars($profile['id']); ?></dd>
                    </div>
                    <div>
                        <dt>Full name</dt>
                        <dd><?php echo htmlspecialchars($profile['fullname']); ?></dd>
                    </div>
                    <div>
                        <dt>Email</dt>
                        <dd><?php echo htmlspecialchars($profile['email']); ?></dd>
                    </div>
                    <div>
                        <dt>Role</dt>
                        <dd><?php echo htmlspecialchars(ucfirst($profile['role'])); ?></dd>
                    </div>
                </dl>
            <?php endif; ?>
        </section>

        <a href="profile-config/edit_profile.php" class="edit-profile-btn">
            ✏️ Edit Profile
        </a>

        <a href="profile-config/delete_account.php"
            class="edit-profile-btn"
            onclick="return confirm('Are you sure you want to permanently delete your account?');">
            🗑️ Delete Account
        </a>

    </main>
</body>
</html>
