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

require_once '../config/database.php';
require_once '../config/image_uploads.php';
require_once 'dashboard_sidebar.php';

$photosByUploader = [];
$galleryError = '';
$trackedFiles = [];

if (empty($_SESSION['pin_csrf_token'])) {
    $_SESSION['pin_csrf_token'] = bin2hex(random_bytes(32));
}

try {
    ensureImageUploadsTable($conn);

    $result = $conn->query(
        'SELECT id, is_pinned, user_id, uploader_name, uploader_email, stored_filename,
                original_filename, uploaded_at
         FROM image_uploads
         ORDER BY uploader_name ASC, uploaded_at DESC'
    );

    if (!$result) {
        throw new RuntimeException('The photo records could not be loaded.');
    }

    while ($photo = $result->fetch_assoc()) {
        $groupKey = (string) $photo['user_id'];
        $trackedFiles[$photo['stored_filename']] = true;

        if (!isset($photosByUploader[$groupKey])) {
            $photosByUploader[$groupKey] = [
                'name' => $photo['uploader_name'],
                'email' => $photo['uploader_email'],
                'photos' => [],
            ];
        }

        $photosByUploader[$groupKey]['photos'][] = $photo;
    }

    $uploadDirectory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads';
    $legacyPhotos = [];

    foreach (glob($uploadDirectory . DIRECTORY_SEPARATOR . '*') ?: [] as $path) {
        $fileName = basename($path);
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (
            is_file($path)
            && in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)
            && !isset($trackedFiles[$fileName])
        ) {
            $legacyPhotos[] = [
                'stored_filename' => $fileName,
                'original_filename' => $fileName,
                'uploaded_at' => date('Y-m-d H:i:s', filemtime($path)),
            ];
        }
    }

    if ($legacyPhotos !== []) {
        $photosByUploader['legacy'] = [
            'name' => 'Earlier uploads',
            'email' => 'Uploader information was not recorded.',
            'photos' => $legacyPhotos,
        ];
    }
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    $galleryError = 'The photo gallery could not be loaded.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Uploads</title>
    <link rel="stylesheet" href="../css/style.css?v=responsive-2">
    
</head>
<body class="with-sidebar">
    <?php renderDashboardSidebar('photos'); ?>

    <main class="gallery">
        <header class="page-header">
            <div>
                <h1>Your Uploads</h1>
                <p>Photos are grouped by the user who uploaded them.</p>
            </div>
        </header>

        <?php if ($galleryError !== ''): ?>
            <p class="message"><?php echo htmlspecialchars($galleryError); ?></p>
        <?php elseif (empty($photosByUploader)): ?>
            <p class="empty">No photos have been uploaded yet.</p>
        <?php else: ?>
            <?php foreach ($photosByUploader as $uploader): ?>
                <section class="uploader-group">
                    <h2><?php echo htmlspecialchars($uploader['name']); ?></h2>
                    <p><?php echo htmlspecialchars($uploader['email']); ?></p>

                    <div class="photo-grid">
                        <?php foreach ($uploader['photos'] as $photo): ?>
                            <figure>
                                <img
                                    src="../uploads/<?php echo rawurlencode($photo['stored_filename']); ?>"
                                    alt="<?php echo htmlspecialchars($photo['original_filename']); ?>"
                                    loading="lazy"
                                >
                                <figcaption>
                                    <span><?php echo htmlspecialchars($photo['original_filename']); ?></span>
                                    <time datetime="<?php echo htmlspecialchars($photo['uploaded_at']); ?>">
                                        <?php echo htmlspecialchars(date('M j, Y g:i A', strtotime($photo['uploaded_at']))); ?>
                                    </time>
                                </figcaption>
                                <?php if (
                                    ($_SESSION['role'] ?? '') === 'artist'
                                    && isset($photo['user_id'])
                                    && (int) $photo['user_id'] === (int) $_SESSION['id']
                                ): ?>
                                    <form action="pin_artwork.php" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['pin_csrf_token']); ?>">
                                        <input type="hidden" name="artwork_id" value="<?php echo htmlspecialchars($photo['id']); ?>">
                                        <input type="hidden" name="is_pinned" value="<?php echo $photo['is_pinned'] ? '0' : '1'; ?>">
                                        <button type="submit">
                                            <?php echo $photo['is_pinned'] ? 'Unpin Artwork' : 'Pin to Profile'; ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </figure>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</body>
</html>
