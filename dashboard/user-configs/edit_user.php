
<?php
session_start();

if (!isset($_SESSION['id'])) {
    header('Location: ../auth/login.php');
    exit;
}

if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);

    header('Location: student_dashboard.php');
    exit('Access denied. Administrators only.');
}

require_once '../../config/database.php';
require_once '../dashboard_sidebar.php';


$userId = $_GET['id'] ?? null;

if (!$userId || !ctype_digit($userId)) {
    http_response_code(400);
    exit('Invalid user ID.');
}

$errorMessage = '';
$successMessage = '';


// =========================
// GET USER INFORMATION
// =========================

$stmt = $conn->prepare(
    'SELECT id, fullname, email, role
     FROM users
     WHERE id = ?'
);

$stmt->bind_param('i', $userId);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();

if (!$user) {
    http_response_code(404);
    exit('User not found.');
}


// =========================
// UPDATE USER
// =========================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fullname = htmlspecialchars(trim($_POST['fullname'] ?? ''));
    $email = htmlspecialchars(trim($_POST['email'] ?? ''));
    $role = $_POST['role'] ?? '';

    // Validate fields
    if ($fullname === '' || $email === '' || $role === '') {

        $errorMessage = 'Please fill in all fields.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $errorMessage = 'Please enter a valid email address.';

    } elseif (!in_array($role, ['admin', 'artist', 'commissioner'], true)) {

        $errorMessage = 'Invalid role selected.';

    } else {

        // Update database
        $stmt = $conn->prepare(
            'UPDATE users
             SET fullname = ?, email = ?, role = ?
             WHERE id = ?'
        );

        $stmt->bind_param(
            'sssi',
            $fullname,
            $email,
            $role,
            $userId
        );

        if ($stmt->execute()) {

            // Update displayed values
            $user['fullname'] = $fullname;
            $user['email'] = $email;
            $user['role'] = $role;

            $successMessage = 'User updated successfully.';

            header("Location: ../users.php");
            exit();
        } else {

            $errorMessage = 'Failed to update user.';
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Edit User</title>

    <link rel="stylesheet" href="../../css/style.css?v=responsive-2">


</head>

<body class="with-sidebar">

    <?php renderDashboardSidebar('users'); ?>

    <main class="page">

        <section class="panel">

            <h1>Edit User</h1>

            <p class="description">
                Edit the information for this user.
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


            <form method="POST">

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

                    <label for="role">
                        Role
                    </label>

                    <select id="role" name="role" required>

                        <option
                            value="artist"
                            <?php echo $user['role'] === 'artist' ? 'selected' : ''; ?>>
                            Artist
                        </option>

                        <option
                            value="commissioner"
                            <?php echo $user['role'] === 'commissioner' ? 'selected' : ''; ?>>
                            Commissioner
                        </option>

                        <option
                            value="admin"
                            <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>
                            Admin
                        </option>

                    </select>

                </div>


                <div class="page-actions">

                    <a
                        href="../users.php"
                        class="back-button">
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="save-button">
                        Save Changes
                    </button>

                </div>

            </form>

        </section>

    </main>

</body>
</html>

