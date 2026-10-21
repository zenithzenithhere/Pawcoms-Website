<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['temp_email'], $_SESSION['temp_password'])) {
    header('Location: register.php');
    exit;
}

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $role = $_POST['role'] ?? '';
    $profilePicture = ''; // An empty value means the default profile image.

    if ($fullname === '' || !in_array($role, ['artist', 'commissioner'], true)) {
        $errorMessage = 'Please enter your name and choose an account type.';
    }

    // Optional profile picture: validate the actual image, not its file extension.
    if ($errorMessage === '' && isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $photo = $_FILES['photo'];
        $types = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
        $imageInfo = $photo['error'] === UPLOAD_ERR_OK ? @getimagesize($photo['tmp_name']) : false;
        $type = $imageInfo['mime'] ?? '';

        if ($photo['error'] !== UPLOAD_ERR_OK || $photo['size'] > 2 * 1024 * 1024 || !isset($types[$type])) {
            $errorMessage = 'Choose a JPG or PNG image smaller than 2 MB.';
        } else {
            $folder = '../profile-photos/';
            if (!is_dir($folder)) {
                mkdir($folder, 0755, true);
            }
            $profilePicture = uniqid('profile_', true) . '.' . $types[$type];
            if (!move_uploaded_file($photo['tmp_name'], $folder . $profilePicture)) {
                $errorMessage = 'Unable to save the profile picture.';
            }
        }
    }

    if ($errorMessage === '') {
        $email = $_SESSION['temp_email'];
        $hashedPassword = $_SESSION['temp_password'];

        // CREATE: Add a new user to the database.
        $stmt = $conn->prepare('INSERT INTO users (fullname, email, password, photo, role) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('sssss', $fullname, $email, $hashedPassword, $profilePicture, $role);

        if ($stmt->execute()) {
            session_regenerate_id(true);
            $_SESSION['id'] = $conn->insert_id;
            $_SESSION['fullname'] = $fullname;
            $_SESSION['email'] = $email;
            $_SESSION['role'] = $role;
            $_SESSION['photo'] = $profilePicture;
            unset($_SESSION['temp_email'], $_SESSION['temp_password']);

            header('Location: ../dashboard/artwork_feed.php?success=registered');
            exit;
        }
        $errorMessage = 'Could not create the account. Try again.';
        if ($profilePicture !== '') {
            @unlink('../profile-photos/' . $profilePicture);
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                    src="../images/default-profile.png"
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
            <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($_POST['fullname'] ?? ''); ?>" required>
        </div>

        

        <div class="form-group">
            <label for="role">Account Type</label>
            <select id="role" name="role" required>
                <option value="">Select Account Type</option>
                <option value="artist" <?php echo ($_POST['role'] ?? '') === 'artist' ? 'selected' : ''; ?>>Artist</option>
                <option value="commissioner" <?php echo ($_POST['role'] ?? '') === 'commissioner' ? 'selected' : ''; ?>>Commissioner</option>
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
