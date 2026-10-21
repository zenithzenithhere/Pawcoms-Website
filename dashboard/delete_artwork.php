<?php
session_start();

if (!isset($_SESSION['id'])) {
    header('Location: ../auth/login.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: artwork_feed.php');
    exit;
}

$token = $_POST['csrf_token'] ?? '';
$id = filter_input(INPUT_POST, 'artwork_id', FILTER_VALIDATE_INT);
if (!is_string($token) || empty($_SESSION['pin_csrf_token']) ||
    !hash_equals($_SESSION['pin_csrf_token'], $token) || !$id) {
    exit('Invalid delete request. Please return to the artwork feed.');
}

require_once '../config/database.php';
$userId = (int) $_SESSION['id'];

// READ: Find the post and current user's role before checking permission.
$stmt = $conn->prepare('SELECT role FROM users WHERE id = ?');
$stmt->bind_param('i', $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$stmt = $conn->prepare('SELECT user_id, stored_filename FROM image_uploads WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$artwork = $stmt->get_result()->fetch_assoc();
$stmt->close();

$role = $user['role'] ?? '';
$isOwner = $artwork && (int) $artwork['user_id'] === $userId;
if (!$artwork || ($role !== 'admin' && !($role === 'artist' && $isOwner))) {
    http_response_code(403);
    exit('You cannot delete this artwork.');
}

// DELETE: An admin may delete any post; artists may only delete their own.
if ($role === 'admin') {
    $stmt = $conn->prepare('DELETE FROM image_uploads WHERE id = ?');
    $stmt->bind_param('i', $id);
} else {
    $stmt = $conn->prepare('DELETE FROM image_uploads WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $id, $userId);
}
$stmt->execute();
$deleted = $stmt->affected_rows > 0;
$stmt->close();

if ($deleted) {
    $path = '../uploads/' . basename($artwork['stored_filename']);
    if (is_file($path)) {
        unlink($path);
    }
    $_SESSION['delete_success'] = 'Artwork deleted successfully!';
}
header('Location: artwork_feed.php');
exit;
