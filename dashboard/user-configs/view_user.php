<?php
session_start();

if (!isset($_SESSION['id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Access denied. Administrators only.');
}

require_once '../../config/database.php';
require_once '../dashboard_sidebar.php';

$userId = $_GET['id'] ?? null;

if (!$userId || !ctype_digit($userId)) {
    http_response_code(400);
    exit('Invalid user ID.');
}

$stmt = $conn->prepare(
    'SELECT id, fullname, email, role
     FROM users
     WHERE id = ?'
);

$stmt->bind_param('i', $userId);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();

if (!$user) {
    http_response_code(404);
    exit('User not found.');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>View User</title>

    <link rel="stylesheet" href="../../css/style.css?v=responsive-2">

</head>

<body class="with-sidebar">

    <?php renderDashboardSidebar('users'); ?>

    <main class="page">

        <section class="panel">

            <h1>View User</h1>

            <div class="user-details">

                <div class="detail-row">
                    <strong>ID</strong>
                    <span>
                        <?php echo htmlspecialchars($user['id']); ?>
                    </span>
                </div>

                <div class="detail-row">
                    <strong>Full Name</strong>
                    <span>
                        <?php echo htmlspecialchars($user['fullname']); ?>
                    </span>
                </div>

                <div class="detail-row">
                    <strong>Email</strong>
                    <span>
                        <?php echo htmlspecialchars($user['email']); ?>
                    </span>
                </div>

                <div class="detail-row">
                    <strong>Role</strong>
                    <span>
                        <?php echo htmlspecialchars(ucfirst($user['role'])); ?>
                    </span>
                </div>

            </div>

            <div class="page-actions">

                <a href="../users.php" class="back-button">
                    ← Back
                </a>

                <a href="edit_user.php?id=<?php echo $user['id']; ?>"
                class="edit-button">
                    Edit
                </a>

            </div>

        </section>

    </main>

</body>
</html>
