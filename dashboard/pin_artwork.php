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

// These hidden values identify the clicked artwork and prevent unwanted requests.
$token = $_POST['csrf_token'] ?? '';
$id = filter_input(INPUT_POST, 'artwork_id', FILTER_VALIDATE_INT);
$pin = $_POST['is_pinned'] ?? '';

if (!is_string($token) || empty($_SESSION['pin_csrf_token']) ||
    !hash_equals($_SESSION['pin_csrf_token'], $token) || !$id ||
    !in_array($pin, ['0', '1'], true)) {
    exit('Invalid pin request. Please return to the artwork feed.');
}

require_once '../config/database.php';
$userId = (int) $_SESSION['id'];

// Artists pin their own artwork; admins can pin any artwork.
$stmt = $conn->prepare('SELECT role FROM users WHERE id = ?');
$stmt->bind_param('i', $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user || !in_array($user['role'], ['artist', 'admin'], true)) {
    http_response_code(403);
    exit('You cannot pin artwork.');
}

// UPDATE: Admins can pin any artwork. Artists can only pin their own.
if ($user['role'] === 'admin') {
    $stmt = $conn->prepare('UPDATE image_uploads SET is_pinned = ? WHERE id = ?');
    $stmt->bind_param('ii', $pin, $id);
} else {
    $stmt = $conn->prepare('UPDATE image_uploads SET is_pinned = ? WHERE id = ? AND user_id = ?');
    $stmt->bind_param('iii', $pin, $id, $userId);
}
$stmt->execute();
$stmt->close();

$back = ($_POST['return_to'] ?? '') === 'dashboard' ? 'artwork_feed.php' : 'photos.php';
header('Location: ' . $back);
exit;
