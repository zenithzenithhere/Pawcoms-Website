<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || trim($password) === '') {
    $_SESSION['errorMessage'] = 'Please enter a valid email and password.';
    header('Location: register.php');
    exit;
}

// READ: Make sure this email is not registered yet.
$stmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$exists = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($exists) {
    $_SESSION['errorMessage'] = 'An account with this email already exists.';
    header('Location: register.php');
    exit;
}

// Keep step 1's details until the user completes step 2.
$_SESSION['temp_email'] = $email;
$_SESSION['temp_password'] = password_hash($password, PASSWORD_DEFAULT);
header('Location: register_process2.php');
exit;
