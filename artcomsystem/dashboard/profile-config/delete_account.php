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


require_once '../../config/database.php';


// Get the currently logged-in user's ID
$userId = (int) $_SESSION['id'];

// Delete the current user's account
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");

if (!$stmt) {
    error_log('Delete profile prepare failed: ' . $conn->error);
    exit('Something went wrong.');
}


$stmt->bind_param('i', $userId);


if ($stmt->execute()) {

    if ($stmt->affected_rows > 0) {

        // Destroy the session after successful deletion
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();

        // Send user back to the public homepage
        header('Location: ../../index.php?status=deleted');
        exit();

    } else {

        // User no longer exists
        exit('Your account could not be found.');

    }

} else {

    error_log('Delete profile failed: ' . $stmt->error);
    exit('Unable to delete your profile.');
}


$stmt->close();
$conn->close();