<?php

session_start();

require_once '../config/database.php';

// Make sure the user is logged in
if (!isset($_SESSION['id'])) {
    header('Location: ../auth/login.php');
    exit();
}

if (!empty($_SESSION['is_blocked'])) {
    http_response_code(403);
    exit('Access denied.');
}


$role = $_SESSION['role'] ?? '';

if (!in_array($role, ['admin', 'artist'], true)) {
    http_response_code(403);
    exit('You do not have permission to delete artworks.');
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: artwork_feed.php');
    exit();
}

// Validate CSRF token
$submittedToken = $_POST['csrf_token'] ?? '';
if (
    !is_string($submittedToken) ||
    empty($_SESSION['pin_csrf_token']) ||
    !hash_equals($_SESSION['pin_csrf_token'], $submittedToken)
) {
    http_response_code(403);
    exit('Invalid request. Please try again.');
}

$artworkId = filter_input(INPUT_POST, 'artwork_id', FILTER_VALIDATE_INT);
$userId = (int) $_SESSION['id'];

if (!$artworkId || $artworkId < 1) {
    header('Location: artwork_feed.php');
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

// Admin can delete any artwork. Artist can delete only their own.
if (!$artwork || ($role !== 'admin' && (int) $artwork['user_id'] !== $userId)) {
    header('Location: artwork_feed.php');
    exit();
}

// Delete the record, using different permissions for admin and artist.
if ($role === 'admin') {
    $stmt = $conn->prepare('DELETE FROM image_uploads WHERE id = ?');
    $stmt->bind_param('i', $artworkId);
} else {
    $stmt = $conn->prepare('DELETE FROM image_uploads WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $artworkId, $userId);
}
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
    
header('Location: artwork_feed.php');
exit();
