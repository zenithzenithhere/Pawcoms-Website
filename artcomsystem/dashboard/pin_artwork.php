<?php
session_start();

if (!isset($_SESSION['id'])) {
    header('Location: ../auth/login.php');
    exit;
}

if (!empty($_SESSION['is_blocked'])) {
    http_response_code(403);
    exit('Access denied.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Please use the artwork pin button.');
}

$submittedToken = $_POST['csrf_token'] ?? '';

if (
    !is_string($submittedToken)
    || empty($_SESSION['pin_csrf_token'])
    || !hash_equals($_SESSION['pin_csrf_token'], $submittedToken)
) {
    http_response_code(403);
    exit('Your session expired. Return to Your Uploads and try again.');
}

$artworkId = $_POST['artwork_id'] ?? '';
$isPinned = $_POST['is_pinned'] ?? '';
$returnTo = $_POST['return_to'] ?? '';

if (!is_string($artworkId) || !ctype_digit($artworkId) || !in_array($isPinned, ['0', '1'], true)) {
    http_response_code(400);
    exit('Invalid artwork selection.');
}

require_once '../config/database.php';

$userId = (int) $_SESSION['id'];

// Check the current role before changing an artwork.
$stmt = $conn->prepare('SELECT role FROM users WHERE id = ?');
$stmt->bind_param('i', $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user || $user['role'] == 'comissioner') {
    http_response_code(403);
    exit('Only artists can pin their own artworks.');
}

// Only update an artwork belonging to the logged-in artist.
$stmt = $conn->prepare(
    'UPDATE image_uploads
     SET is_pinned = ?
     WHERE id = ? AND user_id = ?'
);
$stmt->bind_param('iii', $isPinned, $artworkId, $userId);
$stmt->execute();
$stmt->close();

header('Location: ' . ($returnTo === 'dashboard' ? 'user_dashboard.php' : 'photos.php'));
exit;
