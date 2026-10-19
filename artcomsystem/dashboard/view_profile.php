<?php
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

session_start();

if (!isset($_SESSION['id'])) {
    header('Location: ../auth/login.php');
    exit;
}

if (!empty($_SESSION['is_blocked'])) {
    http_response_code(403);
    exit('Access denied.');
}

$userId = $_GET['id'] ?? '';

if (!is_string($userId) || !ctype_digit($userId)) {
    http_response_code(400);
    exit('Invalid user ID.');
}

require_once '../config/database.php';
require_once '../config/image_uploads.php';
require_once 'dashboard_sidebar.php';

$stmt = $conn->prepare('SELECT id, fullname, role, photo FROM users WHERE id = ?');
$stmt->bind_param('i', $userId);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$profile) {
    http_response_code(404);
    exit('Profile not found.');
}

$artworks = [];
$errorMessage = '';

if ($profile['role'] === 'artist') {
    try {
        ensureImageUploadsTable($conn);
        $stmt = $conn->prepare(
            'SELECT stored_filename, original_filename
             FROM image_uploads
             WHERE user_id = ? AND is_pinned = 1
             ORDER BY uploaded_at DESC, id DESC'
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($artwork = $result->fetch_assoc()) {
            $artworks[] = $artwork;
        }

        $stmt->close();
    } catch (Throwable $exception) {
        error_log($exception->getMessage());
        $errorMessage = 'The pinned artworks could not be loaded.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Profile</title>
    <link rel="stylesheet" href="../css/style.css?v=dashboard-sidebar-1">
</head>
<body class="with-sidebar">
    <?php renderDashboardSidebar('profiles'); ?>

    <main class="gallery">
        <section class="panel">
            <a href="profiles.php" class="back-button">Back to Pinned Artworks</a>
            <h1><?php echo htmlspecialchars($profile['fullname']); ?></h1>
            <?php if (!empty($profile['photo'])): ?>
                <img src="../profile-photos/<?php echo rawurlencode($profile['photo']); ?>" alt="Profile Picture" class="profile-picture" width="150" height="150">
            <?php endif; ?>
            <p><?php echo htmlspecialchars(ucfirst($profile['role'])); ?></p>
        </section>

        <?php if ($profile['role'] === 'artist'): ?>
            <section class="uploader-group">
                <h2>Pinned Artworks</h2>
                <?php if ((int) $profile['id'] === (int) $_SESSION['id']): ?>
                    <p><a href="photos.php">Pin or unpin your uploaded artworks in Your Uploads.</a></p>
                <?php endif; ?>

                <?php if ($errorMessage !== ''): ?>
                    <p class="message"><?php echo htmlspecialchars($errorMessage); ?></p>
                <?php elseif (empty($artworks)): ?>
                    <p>No artworks pinned yet.</p>
                <?php else: ?>
                    <div class="photo-grid">
                        <?php foreach ($artworks as $artwork): ?>
                            <figure>
                                <img src="../uploads/<?php echo rawurlencode($artwork['stored_filename']); ?>" alt="<?php echo htmlspecialchars($artwork['original_filename']); ?>" loading="lazy">
                                <figcaption><?php echo htmlspecialchars($artwork['original_filename']); ?></figcaption>
                            </figure>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>
