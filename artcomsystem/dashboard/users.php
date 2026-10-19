<?php
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

session_start();

if (!isset($_SESSION['id'])) {
    header('Location: ../auth/login.php');
    exit;
}

if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    header('Location: student_dashboard.php');
    exit('Access denied. Administrators only.');
}

require_once '../config/database.php';
require_once 'dashboard_sidebar.php';

$users = [];
$errorMessage = '';
$result = $conn->query(
    'SELECT id, fullname, email, role, photo
     FROM users
     ORDER BY id DESC'
);

if ($result) {
    while ($user = $result->fetch_assoc()) {
        $users[] = $user;
    }
} else {
    error_log('Users query failed: ' . $conn->error);
    $errorMessage = 'The users could not be loaded.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users</title>

    <link rel="stylesheet" href="../css/style.css?v=dashboard-sidebar-1">
    

</head>
<body class="with-sidebar">
    <?php renderDashboardSidebar('users'); ?>

    <main class="page">
        <section class="panel">
            <h1>Users</h1>
            <p class="description">All registered accounts in the system.</p>

            <?php if ($errorMessage !== ''): ?>
                <p class="message"><?php echo htmlspecialchars($errorMessage); ?></p>
            <?php elseif ($users === []): ?>
                <p>No users found.</p>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Profile Picture</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?> 
                                <tr>
                                    <td><?php echo htmlspecialchars($user['id']); ?></td>


                                    <?php
                                    $profileImage = !empty($user['photo'])
                                        ? '../profile-photos/' . $user['photo']
                                        : '../images/default-profile.png';
                                    ?>
                                    <td><img src="<?php echo htmlspecialchars($profileImage); ?>" 
                                            alt="Profile Picture"
                                            class="profile-picture"
                                            width="64"
                                            height="64"></td>


                                    <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars(ucfirst($user['role'])); ?></td>

                                    <td class="actions">
                                        <a href="user-configs/view_user.php?id=<?php echo $user['id']; ?>" class="btn-view">
                                            👁️
                                        </a>

                                        <a href="user-configs/edit_user.php?id=<?php echo $user['id']; ?>" class="btn-edit">
                                            ✏️
                                        </a>

                                        <a href="user-configs/delete_user.php?id=<?php echo $user['id']; ?>"
                                        class="btn-delete"
                                        onclick="return confirm('Are you sure you want to delete this user?');">
                                            🗑️
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
