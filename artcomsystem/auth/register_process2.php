<?php
session_start();
require_once '../config/database.php';

// Security check: If Step 1 wasn't completed, kick them back to Step 1
if (!isset($_SESSION['temp_email']) || !isset($_SESSION['temp_password'])) {
    header('Location: register_step1.php');
    exit;
}

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = htmlspecialchars(trim($_POST['fullname'] ?? ''));
    $role = $_POST['role'] ?? '';
    $profilePicture = 'default-profile.png'; // Default fallback

    // Handle profile picture upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        $fileType = mime_content_type($_FILES['photo']['tmp_name']);

        if (!in_array($fileType, $allowedTypes)) {
            $errorMessage = 'Only JPG, JPEG, and PNG images are allowed.';
        } else {
            $uploadFolder = '../profile-photos/';
            if (!is_dir($uploadFolder)) {
                mkdir($uploadFolder, 0777, true);
            }

            $extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $newFileName = uniqid('profile_', true) . '.' . $extension;

            if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadFolder . $newFileName)) {
                $profilePicture = $newFileName;
            } else {
                $errorMessage = 'Failed to upload profile picture.';
            }
        }
    }

    if (empty($fullname)) {
        $errorMessage = 'Please enter your full name.';
    } elseif (!in_array($role, ['artist', 'commissioner'], true)) {
        $errorMessage = 'Please select a valid account type.';
    }

    if (empty($errorMessage)) {
        // Pull email and password out of the session
        $email = $_SESSION['temp_email'];
        $hashedPassword = $_SESSION['temp_password'];

        // Insert everything into the database all at once
        $stmt = $conn->prepare(
            'INSERT INTO users (fullname, email, password, photo, role) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('sssss', $fullname, $email, $hashedPassword, $profilePicture, $role);

        if ($stmt->execute()) {

        // Get the ID of the newly inserted user
            $newUserId = $conn->insert_id;

            // Automatically log the user in by setting session variables
            $_SESSION['id'] = $newUserId;
            $_SESSION['fullname'] = $fullname;
            $_SESSION['email'] = $email;
            $_SESSION['role'] = $role;
            
            // Clear temporary session data
            unset($_SESSION['temp_email']);
            unset($_SESSION['temp_password']);

            // Redirect to login or dashboard
            header('Location: ../dashboard/user_dashboard.php?success=registered');
            exit;
        } else {
            $errorMessage = 'Database error: Could not register user.';
        }
        $stmt->close();
    }
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Step 2</title>
    <link rel="stylesheet" href="../css/style.css">
</head>



<body>

<div class="register-card">
    <form method="POST" enctype="multipart/form-data">
        <h2>Create Profile</h2>

        <?php if ($errorMessage !== ''): ?>
            <p class="error"><?php echo htmlspecialchars($errorMessage); ?></p>
        <?php endif; ?>

        <div class="form-group profile-upload">
            <label for="photo">Profile Picture</label>

            <!-- Preview Container -->
            <div class="profile-preview-container">
                <img 
                    id="profilePreview"
                    src="/../images/default-profile.png"
                    alt="Profile Preview"
                    class="profile-picture"

                    width="150"
                    height="150"
                >
            </div>

            <!-- File input with an id to track changes -->
            <input
                type="file"
                id="photo"
                name="photo"
                accept="image/png, image/jpeg, image/jpg"
            >
        </div>

        <div class="form-group">
            <label for="fullname">Full Name</label>
            <input type="text" id="fullname" name="fullname" required>
        </div>

        

        <div class="form-group">
            <label for="role">Account Type</label>
            <select id="role" name="role" required>
                <option value="">Select Account Type</option>
                <option value="artist">Artist</option>
                <option value="commissioner">Commissioner</option>
            </select>
        </div>

        <button type="submit">Complete Registration</button>
    </form>
</div>

<script>

    document.getElementById('photo').addEventListener('change', function(event) {
        const file = event.target.files[0];

        if (file) {
            const reader = new FileReader();

            reader.onload = function(e) {
                // Find the image element and change its source to the uploaded file data
                document.getElementById('profilePreview').src = e.target.result;
            };

            // Read the image file as a data URL
            reader.readAsDataURL(file);
        }
    });
</script>
</body>
</html>
