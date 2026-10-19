<?php

// Prevent browser caching
    header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
    header("Pragma: no-cache"); // HTTP 1.0
    header("Expires: 0"); // Proxies

session_start();

    if (basename($_SERVER['PHP_SELF']) == 'nicetry.php') {
    
        die("Access Denied: You are not permitted to view this page.");
    }
    if (isset($_SESSION['is_blocked']) && $_SESSION['is_blocked'] === true) {
        
        die("Access Denied: You are not permitted to view this page.");
    }

    
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="register-card">
    <h2>Create Account</h2>

    <?php if (isset($_SESSION['errorMessage'])): ?>

        <p class="error-message">
            <?php echo htmlspecialchars($_SESSION['errorMessage']); ?>
        </p>

        <?php unset($_SESSION['errorMessage']); ?>


    <?php endif; ?>

    <form action="register_process.php" method="POST">

        <div class="form-group">
            
            <input type="email" id="email" name="email" placeholder="Email Address" required>
        </div>

        <div class="form-group">
            
            <input type="password" id="password" name="password" placeholder="Password" required>
        </div>

        <button type="submit" class="btn-register">Continue</button>
    </form>

    <div class="login-link">
        Already have an account? <a href="login.php">Login</a>
    </div>
</div>

</body>
</html>
