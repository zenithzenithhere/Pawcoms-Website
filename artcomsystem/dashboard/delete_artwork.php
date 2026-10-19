
<?php

session_start();

require_once '../config/database.php';

// Make sure the user is logged in
if (!isset($_SESSION['id'])) {
    header('Location: ../auth/login.php');
    exit();
}


if (($_SESSION['role'] ?? '') == 'comissioner') {
    header('Location: user_dashboard.php');
    exit();
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: user_dashboard.php');
    exit();
}

// Validate CSRF token
if (
    !isset($_POST['csrf_token'], $_SESSION['pin_csrf_token']) ||
    !hash_equals($_SESSION['pin_csrf_token'], $_POST['csrf_token'])
) {
    die('Invalid request. Please try again.');
}

$artworkId = filter_input(INPUT_POST, 'artwork_id', FILTER_VALIDATE_INT);
$userId = (int) $_SESSION['id'];

if (!$artworkId || $artworkId < 1) {
    header('Location: user_dashboard.php');
    exit();
}

// Find the artwork and verify ownership
$stmt = $conn->prepare(
    'SELECT user_id, stored_filename
     FROM image_uploads
     WHERE id = ?
     LIMIT 1'
);

$stmt->bind_param('i', $artworkId);
$stmt->execute();

$artwork = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Prevent deleting another user's artwork
if (!$artwork || (int) $artwork['user_id'] !== $userId) {
    header('Location: user_dashboard.php');
    exit();
}

// Delete the database record
$stmt = $conn->prepare(
    'DELETE FROM image_uploads
     WHERE id = ? AND user_id = ?'
);

$stmt->bind_param('ii', $artworkId, $userId);
$stmt->execute();

$deleted = $stmt->affected_rows === 1;
$stmt->close();

// Delete the image file only after the database deletion succeeds
if ($deleted) {
    $filename = basename($artwork['stored_filename']);
    $imagePath = __DIR__ . '/../uploads/' . $filename;

    if (is_file($imagePath)) {
        unlink($imagePath);
    }

    $_SESSION['delete_success'] = 'Artwork deleted successfully!';
}
    
header('Location: user_dashboard.php');
exit();
?>
