<?php 

    // Prevent browser caching
        header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
        header("Pragma: no-cache"); // HTTP 1.0
        header("Expires: 0"); // Proxies

session_start();


    // Blocked Checker
        if (basename($_SERVER['PHP_SELF']) == 'nicetry.php') {
        
            die("Access Denied: You are not permitted to view this page.");
        }
        if (isset($_SESSION['is_blocked']) && $_SESSION['is_blocked'] === true) {
            
            die("Access Denied: You are not permitted to view this page.");
        }

    // Check if the user is logged
    if (!isset($_SESSION['id'])) {
        header("Location: ../auth/login.php");
        exit();
    }
    

require_once '../config/database.php';
require_once '../config/image_uploads.php';
require_once 'dashboard_sidebar.php';

if (empty($_SESSION['pin_csrf_token'])) {
    $_SESSION['pin_csrf_token'] = bin2hex(random_bytes(32));
}


    // Get the current user's information
    $userId = (int) $_SESSION['id'];

    $stmt = $conn->prepare(
        'SELECT id, fullname, email, role
        FROM users
        WHERE id = ?
        LIMIT 1'
    );

    $stmt->bind_param('i', $userId);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $stmt->close();

    // Load recent artworks for the dashboard feed.
    $artworks = [];
    $feedError = '';

    try {
        ensureImageUploadsTable($conn);
        $artworkResult = $conn->query(
            'SELECT image_uploads.id, image_uploads.user_id, image_uploads.is_pinned,
                    image_uploads.artwork_title, image_uploads.stored_filename,
                    image_uploads.uploader_name, image_uploads.uploaded_at,
                    users.photo
             FROM image_uploads
             LEFT JOIN users ON users.id = image_uploads.user_id
             ORDER BY image_uploads.uploaded_at DESC'
        );

        if (!$artworkResult) {
            throw new RuntimeException('The artworks could not be loaded.');
        }

        while ($artwork = $artworkResult->fetch_assoc()) {
            $artworks[] = $artwork;
        }
    } catch (Throwable $exception) {
        error_log($exception->getMessage());
        $feedError = 'The artwork feed could not be loaded.';
    }


    // Make sure the account still exists
    if (!$user) {
        session_destroy();

        header('Location: ../auth/login.php');
        exit();
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

    <title>User Dashboard</title>

    <link rel="stylesheet" href="../css/style.css?v=dashboard-pin-1">


</head>


<body class="with-sidebar dashboard-feed">

    <?php renderDashboardSidebar('dashboard'); ?>


    <main class="page">

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
                            <div>
                                <strong><?php echo htmlspecialchars($artwork['uploader_name']); ?></strong>
                                <h3><?php echo htmlspecialchars($artwork['artwork_title']); ?></h3>
                            </div>
                            <?php if (
                                $user['role'] === 'artist'
                                && (int) $artwork['user_id'] === $userId
                            ): ?>
                                <form class="artwork-pin-form" action="pin_artwork.php" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['pin_csrf_token']); ?>">
                                    <input type="hidden" name="artwork_id" value="<?php echo htmlspecialchars($artwork['id']); ?>">
                                    <input type="hidden" name="is_pinned" value="<?php echo $artwork['is_pinned'] ? '0' : '1'; ?>">
                                    <input type="hidden" name="return_to" value="dashboard">
                                    <button
                                        type="submit"
                                        class="artwork-pin-button<?php echo $artwork['is_pinned'] ? ' is-pinned' : ''; ?>"
                                        aria-pressed="<?php echo $artwork['is_pinned'] ? 'true' : 'false'; ?>"
                                        aria-label="<?php echo $artwork['is_pinned'] ? 'Unpin artwork' : 'Pin artwork'; ?>"
                                        title="<?php echo $artwork['is_pinned'] ? 'Unpin artwork' : 'Pin artwork'; ?>"
                                    >
                                        <span aria-hidden="true">📌</span>
                                        <?php echo $artwork['is_pinned'] ? 'Unpin' : 'Pin'; ?>
                                    </button>
                                </form>
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


