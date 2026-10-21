<?php
session_start();
if (!isset($_SESSION['id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../config/database.php';
require_once '../dashboard_sidebar.php';
$userId = (int) $_SESSION['id'];
$errorMessage = '';

// READ: Load the logged-in user's current details.
$stmt = $conn->prepare('SELECT fullname, email, password, photo FROM users WHERE id = ?');
$stmt->bind_param('i', $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$user) {
    exit('User not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $picture = $user['photo'];
    $newFile = '';

    if ($fullname === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = 'Please enter your name and a valid email.';
    }

    // A blank password means "keep the current password".
    $hashedPassword = trim($password) === '' ? $user['password'] : password_hash($password, PASSWORD_DEFAULT);

    // Optional profile picture update.
    if ($errorMessage === '' && isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $photo = $_FILES['photo'];
        $types = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
        $imageInfo = $photo['error'] === UPLOAD_ERR_OK ? @getimagesize($photo['tmp_name']) : false;
        $type = $imageInfo['mime'] ?? '';

        if ($photo['error'] !== UPLOAD_ERR_OK || $photo['size'] > 2 * 1024 * 1024 || !isset($types[$type])) {
            $errorMessage = 'Choose a JPG or PNG image smaller than 2 MB.';
        } else {
            $folder = '../../profile-photos/';
            if (!is_dir($folder)) {
                mkdir($folder, 0755, true);
            }
            $newFile = uniqid('profile_', true) . '.' . $types[$type];
            if (move_uploaded_file($photo['tmp_name'], $folder . $newFile)) {
                $picture = $newFile;
            } else {
                $errorMessage = 'Could not save the profile picture.';
            }
        }
    }

    if ($errorMessage === '') {
        // UPDATE: Change this user's information in MySQL.
        $stmt = $conn->prepare('UPDATE users SET fullname = ?, email = ?, password = ?, photo = ? WHERE id = ?');
        $stmt->bind_param('ssssi', $fullname, $email, $hashedPassword, $picture, $userId);
        try {
            $stmt->execute();
            $_SESSION['fullname'] = $fullname;
            $_SESSION['email'] = $email;
            $_SESSION['photo'] = $picture;
            $_SESSION['profile_success'] = 'Profile edited successfully!';
            header('Location: ../profile.php');
            exit;
        } catch (Throwable $error) {
            error_log($error->getMessage());
            $errorMessage = 'Could not update your profile.';
            if ($newFile !== '') {
                @unlink('../../profile-photos/' . $newFile);
            }
        }
        $stmt->close();
    }
    // Keep valid submitted text visible when the form reports an error.
    $user['fullname'] = $fullname;
    $user['email'] = $email;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Profile</title>

    <link rel="stylesheet" href="../../css/style.css?v=responsive-2">

</head>
<body class="with-sidebar">

    <?php renderDashboardSidebar('profile'); ?>

    <main class="page">

        <section class="panel">

            <h1>Edit Profile</h1>
            <p class="description">
                Update your account information.
            </p>


            <?php if ($errorMessage !== ''): ?>
                <p class="message error">
                    <?php echo htmlspecialchars($errorMessage); ?>
                </p>
            <?php endif; ?>





            <form method="POST" enctype="multipart/form-data">

                <div class="form-group profile-upload">

                    <label for="photo">Profile Picture</label>

                
                    <?php
                    $profileImage = !empty($user['photo'])
                        ? '../../profile-photos/' . $user['photo']
                        : '../../images/default-profile.png';
                    ?>

                    <div class="profile-preview-container">

                        <img 
                            id="profilePreview"
                            src="<?php echo htmlspecialchars($profileImage); ?>"
                            alt="Profile Picture"
                            class="profile-picture"
                            width="150"
                            height="150"
                        >

                    </div>

                    <input
                        type="file"
                        id="photo"
                        name="photo"
                        accept="image/png, image/jpeg, image/jpg"
                    >

                </div>

                <div class="form-group">

                    <label for="fullname">
                        Full Name
                    </label>

                    <input
                        type="text"
                        id="fullname"
                        name="fullname"
                        value="<?php echo htmlspecialchars($user['fullname']); ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="email">
                        Email
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?php echo htmlspecialchars($user['email']); ?>"
                        required
                    >

                </div>

                <div class="form-group">

                    <label for="password">
                        New Password
                    </label>

                    <input
                        type="password" 
                        id="password"
                        name="password"
                        placeholder="leave empty if no new password"
                        value=""
                            
                    >

                </div>


                <div class="page-actions">

                    <a href="../profile.php" class="back-button">
                        Cancel
                    </a>

                    <button type="submit" class="save-button">
                        Save Changes
                    </button>

                </div>

            </form>

        </section>

    </main>

<script>
// Show the selected profile photo before submitting the form.
document.getElementById('photo').addEventListener('change', function () {
    const file = this.files[0];
    if (file) {
        document.getElementById('profilePreview').src = URL.createObjectURL(file);
    }
});
</script>
</body>
</html>
