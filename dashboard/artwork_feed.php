<?php
header('Cache-Control: no-cache, no-store, must-revalidate');
session_start();

if (!isset($_SESSION['id'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../config/database.php';
require_once '../config/image_uploads.php';
require_once 'dashboard_sidebar.php';

if (empty($_SESSION['pin_csrf_token'])) {
    $_SESSION['pin_csrf_token'] = bin2hex(random_bytes(16));
}

$userId = (int) $_SESSION['id'];
$stmt = $conn->prepare('SELECT id, role FROM users WHERE id = ?');
$stmt->bind_param('i', $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$user) {
    session_destroy();
    header('Location: ../auth/login.php');
    exit;
}

// READ: Get all artworks, including each artist's profile picture.
$artworks = [];
$feedError = '';
try {
    ensureImageUploadsTable($conn);
    $result = $conn->query(
        'SELECT image_uploads.*, users.photo
         FROM image_uploads
         LEFT JOIN users ON users.id = image_uploads.user_id
         ORDER BY image_uploads.uploaded_at DESC'
    );
    while ($artwork = $result->fetch_assoc()) {
        $artworks[] = $artwork;
    }
} catch (Throwable $error) {
    error_log($error->getMessage());
    $feedError = 'Unable to load the artwork feed.';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Artwork Feed</title>

    <link rel="stylesheet" href="../css/style.css?v=artwork-buttons-fix-1">


</head>


<body class="with-sidebar dashboard-feed">

    <?php renderDashboardSidebar('artwork_feed'); ?>


    <main class="page">

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert-success"><?php echo htmlspecialchars($_SESSION['success']); ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['delete_success'])): ?>
            <div class="alert-success">
                <?= htmlspecialchars($_SESSION['delete_success']) ?>
            </div>
            <?php unset($_SESSION['delete_success']); ?>
        <?php endif; ?>

        <!-- Artwork Feed -->
        <section class="artwork-feed" aria-labelledby="artwork-feed-title">
            <header class="artwork-feed-heading">
                <h2 id="artwork-feed-title">Artwork Feed</h2>
                <p>See the latest work shared by artists.</p>
            </header>

            <?php if ($feedError !== ''): ?>
                <p class="message"><?php echo htmlspecialchars($feedError); ?></p>
            <?php elseif (empty($artworks)): ?>
                <p class="empty">No artworks have been shared yet.</p>
            <?php else: ?>
                <?php foreach ($artworks as $artwork): ?>
                    <article class="artwork-post">
                        <header class="artwork-post-header">
                            <?php if (!empty($artwork['photo'])): ?>
                                <img class="artwork-avatar" src="../profile-photos/<?php echo rawurlencode($artwork['photo']); ?>" alt="">
                            <?php else: ?>
                                <span class="artwork-avatar artwork-avatar-fallback" aria-hidden="true">
                                    <?php echo htmlspecialchars(strtoupper(substr($artwork['uploader_name'], 0, 1))); ?>
                                </span>
                            <?php endif; ?>
                            <div class="artwork-author">
                                <strong><?php echo htmlspecialchars($artwork['uploader_name']); ?></strong>
                                <h3><?php echo htmlspecialchars($artwork['artwork_title']); ?></h3>
                            </div>
                            <?php
                            $isOwner = (int) $artwork['user_id'] === $userId;
                            $canDelete = $user['role'] === 'admin' || ($user['role'] === 'artist' && $isOwner);
                            $canPin = $user['role'] === 'admin' || ($user['role'] === 'artist' && $isOwner);
                            ?>
                            <?php if ($canDelete || $canPin): ?>
                                <div class="artwork-actions">
                                    <?php if ($canPin): ?>
                                        <!-- Pin changes the pin status of this artwork. -->
                                        <form action="pin_artwork.php" method="POST">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['pin_csrf_token']); ?>">
                                            <input type="hidden" name="artwork_id" value="<?php echo (int) $artwork['id']; ?>">
                                            <input type="hidden" name="is_pinned" value="<?php echo $artwork['is_pinned'] ? '0' : '1'; ?>">
                                            <input type="hidden" name="return_to" value="dashboard">
                                            <button type="submit" class="artwork-pin-button<?php echo $artwork['is_pinned'] ? ' is-pinned' : ''; ?>"
                                                aria-pressed="<?php echo $artwork['is_pinned'] ? 'true' : 'false'; ?>">
                                                <span aria-hidden="true">📌</span>
                                                <?php echo $artwork['is_pinned'] ? 'Unpin' : 'Pin'; ?>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($canDelete): ?>
                                        <!-- Delete only shows for the owner or an admin. -->
                                        <form action="delete_artwork.php" method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this artwork?');">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['pin_csrf_token']); ?>">
                                            <input type="hidden" name="artwork_id" value="<?php echo (int) $artwork['id']; ?>">
                                            <button type="submit" class="artwork-delete-button">
                                                <span aria-hidden="true">🗑</span> Delete
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </header>
                        <img class="artwork-image" src="../uploads/<?php echo rawurlencode($artwork['stored_filename']); ?>" alt="<?php echo htmlspecialchars($artwork['artwork_title']); ?>" loading="lazy">
                        <p class="artwork-date">Shared <?php echo htmlspecialchars(date('M j, Y g:i A', strtotime($artwork['uploaded_at']))); ?></p>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

    </main>

</body>

</html>


