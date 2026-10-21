<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    $_SESSION['errorMessage'] = 'Please fill out the form.';
    header('Location: login.php');
    exit;
}

// READ: Find the user by their email.
$stmt = $conn->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user || !password_verify($password, $user['password'])) {
    $_SESSION['errorMessage'] = 'Incorrect email or password.';
    header('Location: login.php');
    exit;
}

// Store the logged-in user's identity in the session.
session_regenerate_id(true);
$_SESSION['id'] = $user['id'];
$_SESSION['fullname'] = $user['fullname'];
$_SESSION['email'] = $user['email'];
$_SESSION['role'] = $user['role'];
$_SESSION['photo'] = $user['photo'];

// Administrators open their dashboard; everyone else opens the feed.
if ($user['role'] === 'admin') {
    header('Location: ../dashboard/admin_dashboard.php');
} else {
    header('Location: ../dashboard/artwork_feed.php');
}
exit;
