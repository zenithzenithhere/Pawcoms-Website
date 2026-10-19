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

$artists = [];
$errorMessage = '';
$artistRole = 'artist'; // Change this to 'artist' if your database specifically uses that role

$stmt = $conn->prepare(
    'SELECT id, fullname, email
     FROM users
     WHERE role = ?
     ORDER BY fullname ASC'
);

if ($stmt) {
    // Fixed: Now matches $artistRole instead of undefined $studentRole
    $stmt->bind_param('s', $artistRole);

    if ($stmt->execute()) {
        $result = $stmt->get_result();

        // Fixed: Correctly push fetched rows into the $artists array
        while ($row = $result->fetch_assoc()) {
            $artists[] = $row;
        }
    } else {
        error_log('Artists query failed: ' . $stmt->error);
        $errorMessage = 'The artists could not be loaded.';
    }

    $stmt->close();
} else {
    error_log('Artists query failed: ' . $conn->error);
    $errorMessage = 'The artists could not be loaded.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artists</title>
    <link rel="stylesheet" href="../css/style.css?v=dashboard-sidebar-1">
    
</head>
<body class="with-sidebar">
    <?php renderDashboardSidebar('artists'); ?>

    <main class="page">
        <section class="panel">
            
            <h1>Artists</h1>
            <p class="description">Discover Artists!</p>

            <?php if ($errorMessage !== ''): ?>
                <p class="message"><?php echo htmlspecialchars($errorMessage); ?></p>
            <?php elseif ($artists === []): ?>
                
                <p>No artists found.</p>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($artists as $artist): ?>
                                <tr>
                                    
                                    <td><?php echo htmlspecialchars($artist['fullname']); ?></td>
                                    <td><?php echo htmlspecialchars($artist['email']); ?></td>
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
