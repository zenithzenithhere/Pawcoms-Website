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
$successMessage = '';


// Get the current user's information
$stmt = $conn->prepare(
    'SELECT id, fullname, email, password, role, photo
     FROM users
     WHERE id = ?
     LIMIT 1'
);

$stmt->bind_param('i', $userId);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();

if (!$user) {
    exit('User not found.');
}


// Update profile when form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fullname = htmlspecialchars(trim($_POST['fullname'] ?? ''));
    $email = trim($_POST['email'] ?? '');
    $profilePicture = $user['photo'];


            if (
                isset($_FILES['photo']) &&
                $_FILES['photo']['error'] === UPLOAD_ERR_OK
            ) {

                $allowedTypes = ['image/jpeg', 'image/png'];

                $fileType = mime_content_type(
                    $_FILES['photo']['tmp_name']
                );

                if (!in_array($fileType, $allowedTypes)) {

                    $errorMessage = 'Only JPG, JPEG, and PNG images are allowed.';

                } else {

                    $uploadFolder = '../../profile-photos/';

                    // Create folder if it doesn't exist
                    if (!is_dir($uploadFolder)) {
                        mkdir($uploadFolder, 0777, true);
                    }

                    // Get file extension
                    $extension = pathinfo(
                        $_FILES['photo']['name'],
                        PATHINFO_EXTENSION
                    );

                    // Create unique filename
                    $newFileName = uniqid('profile_', true) . '.' . $extension;

                    // Upload image
                    if (move_uploaded_file(
                        $_FILES['photo']['tmp_name'],
                        $uploadFolder . $newFileName
                    )) {

                        $profilePicture = $newFileName;

                    } else {

                        $errorMessage = 'Failed to upload profile picture.';
                    }
                }
            }
    

    // Check if fields are empty
    if (empty($fullname) || empty($email)) {

        $errorMessage = 'Please fill in all fields.';

    // Check if email is valid
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $errorMessage = 'Please enter a valid email address.';

    } else {
    
        $password = $_POST['password'] ?? '';

        if (empty(trim($password))) {
            // If empty, keep the existing password from the database
            $hashedPassword = $user['password'];
        } else {
            // If a new password was entered, hash the new one
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        }

        $stmt = $conn->prepare(
            'UPDATE users
             SET fullname = ?, email = ?, password = ?, photo = ?
             WHERE id = ?'
        );

        $stmt->bind_param(
            'ssssi',
            $fullname,
            $email,
            $hashedPassword,
            $profilePicture,
            $userId
        );

        if ($stmt->execute()) {

            // Update session information
            $_SESSION['fullname'] = $fullname;
            $_SESSION['email'] = $email;
            $_SESSION['password'] =
        
            

            $successMessage = 'Profile updated successfully.';

            // Update displayed values
            $user['fullname'] = $fullname;
            $user['email'] = $email;    

            $stmt->close();

            $_SESSION['profile_success'] = "Profile edited successfully!";

            header('Location: ../profile.php?success=1');
            exit;

            

        } else {
            $errorMessage = 'Failed to update profile.';
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

    <title>Edit Profile</title>

    <link rel="stylesheet" href="../../css/style.css?v=responsive-2">

</head>

<script>
document.getElementById('profile_picture').addEventListener('change', function(event) {

    const file = event.target.files[0];

    if (file) {

        const reader = new FileReader();

        reader.onload = function(e) {

            document.getElementById('profilePreview').src = e.target.result;

        };

        reader.readAsDataURL(file);
    }

});
</script>

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


            <?php if ($successMessage !== ''): ?>
                <p class="message success">
                    <?php echo htmlspecialchars($successMessage); ?>
                </p>
            <?php endif; ?>


            <form method="POST" enctype="multipart/form-data">

                <div class="form-group profile-upload">

                    <label for="profile_picture">Profile Picture</label>

                
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

</body>
</html>
