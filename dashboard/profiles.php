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
require_once '../config/image_uploads.php';
require_once 'dashboard_sidebar.php';

$profiles = [];
$errorMessage = '';

try {
    ensureImageUploadsTable($conn);
    $result = $conn->query(
        "SELECT users.id, users.fullname, users.role, users.photo,
                image_uploads.stored_filename, image_uploads.original_filename,
                image_uploads.artwork_title
         FROM users
         LEFT JOIN image_uploads
            ON image_uploads.user_id = users.id
            AND image_uploads.is_pinned = 1
         WHERE users.role IN ('artist', 'admin')
         ORDER BY users.fullname ASC, image_uploads.uploaded_at DESC, image_uploads.id DESC"
    );

    if (!$result) {
        throw new RuntimeException('The pinned artworks could not be loaded.');
    }

    while ($profile = $result->fetch_assoc()) {
        $profileId = (string) $profile['id'];

        if (!isset($profiles[$profileId])) {
            $profiles[$profileId] = [
                'id' => $profile['id'],
                'fullname' => $profile['fullname'],
                'role' => $profile['role'],
                'photo' => $profile['photo'],
                'artworks' => [],
            ];
        }

        if (!empty($profile['stored_filename'])) {
            $profiles[$profileId]['artworks'][] = [
                'stored_filename' => $profile['stored_filename'],
                'original_filename' => $profile['original_filename'],
                'artwork_title' => $profile['artwork_title'],
            ];
        }
    }
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    $errorMessage = 'The profiles could not be loaded.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pinned Artworks</title>
    <link rel="stylesheet" href="../css/style.css?v=responsive-2">
</head>
<body class="with-sidebar">
    <?php renderDashboardSidebar('profiles'); ?>

    <main class="page">
        <?php if ($errorMessage !== ''): ?>
            <p class="message"><?php echo htmlspecialchars($errorMessage); ?></p>
        <?php elseif (empty($profiles)): ?>
            <p class="empty">No artists found.</p>
        <?php endif; ?>

        <?php if ($errorMessage === '' && !empty($profiles)): ?>
            <?php foreach ($profiles as $profile): ?>
                <section class="pinned-artist">
                    <header class="page-header">
                        <?php if (!empty($profile['photo'])): ?>
                            <img
                                src="../profile-photos/<?php echo rawurlencode($profile['photo']); ?>"
                                alt="<?php echo htmlspecialchars($profile['fullname']); ?> profile picture"
                                width="64"
                                height="64"
                            >
                        <?php endif; ?>
                        <div>
                            <h1><?php echo htmlspecialchars($profile['fullname']); ?></h1>
                        </div>
                    </header>

                    <section class="uploader-group">
                        <h2>Pinned Artworks</h2>
                        <?php if (empty($profile['artworks'])): ?>
                            <p>No artworks pinned yet.</p>
                        <?php else: ?>
                            <div class="photo-grid">
                                <?php foreach ($profile['artworks'] as $artwork): ?>
                                    <?php $artworkTitle = $artwork['artwork_title'] !== '' ? $artwork['artwork_title'] : $artwork['original_filename']; ?>
                                    <figure>
                                        <img
                                            src="../uploads/<?php echo rawurlencode($artwork['stored_filename']); ?>"
                                            alt="<?php echo htmlspecialchars($artworkTitle); ?>"
                                            loading="lazy"
                                        >
                                        <figcaption><?php echo htmlspecialchars($artworkTitle); ?></figcaption>
                                    </figure>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>
                </section>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</body>
</html>
