<?php

session_start();

// Prevent caching
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');


// Make sure the user is logged in
if (!isset($_SESSION['id'])) {
    header('Location: ../../auth/login.php');
    exit();
}


// Make sure the current user is an admin
if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Access denied. Administrators only.');
}


require_once '../../config/database.php';


// Get the user ID from the URL
$userId = $_GET['id'] ?? '';


// Check if an ID was provided
if (empty($userId) || !is_numeric($userId)) {
    header('Location: ../users.php?status=invalid');
    exit();
}

$userId = (int) $userId;


// Prevent an admin from deleting their own account
if ($userId === (int) $_SESSION['id']) {
    header('Location: ../users.php?status=self_delete');
    exit();
}


// Delete the user
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);


if ($stmt->execute()) {

    if ($stmt->affected_rows > 0) {
        // User successfully deleted
        header('Location: ../users.php?status=deleted');
        exit();
    } else {
        // User ID doesn't exist
        header('Location: ../users.php?status=not_found');
        exit();
    }

} else {

    error_log('Delete user failed: ' . $stmt->error);

    header('Location: ../users.php?status=error');
    exit();
}


$stmt->close();
$conn->close();