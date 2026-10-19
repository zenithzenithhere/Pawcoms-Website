<?php
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

session_start();

if (!isset($_SESSION['id'])) {
    header('Location: ../auth/login.php');
    exit;
}

if (!empty($_SESSION['is_blocked'])) {
    http_response_code(403);
    exit('Access denied.');
}

require_once '../config/database.php';
require_once '../config/image_uploads.php';
require_once 'dashboard_sidebar.php';

if (empty($_SESSION['upload_csrf_token'])) {
    $_SESSION['upload_csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';
$messageType = '';
$uploadedImage = '';
$artworkTitle = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = $_POST['csrf_token'] ?? '';
    $submittedTitle = $_POST['artwork_title'] ?? '';
    $artworkTitle = is_string($submittedTitle) ? trim($submittedTitle) : '';

    if (!hash_equals($_SESSION['upload_csrf_token'], $submittedToken)) {
        $message = 'Your session expired. Refresh the page and try again.';
        $messageType = 'error';
    } elseif ($artworkTitle === '') {
        $message = 'Please enter a title for the artwork.';
        $messageType = 'error';
    } elseif (!isset($_FILES['image'])) {
        $message = 'Please choose an image.';
        $messageType = 'error';
    } elseif ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => 'The image is larger than the server allows.',
            UPLOAD_ERR_FORM_SIZE => 'The image is too large.',
            UPLOAD_ERR_PARTIAL => 'The image was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'Please choose an image.',
        ];
        $message = $uploadErrors[$_FILES['image']['error']] ?? 'The image could not be uploaded.';
        $messageType = 'error';
    } elseif ($_FILES['image']['size'] > 2 * 1024 * 1024) {
        $message = 'The image must be 2 MB or smaller.';
        $messageType = 'error';
    } elseif (!is_uploaded_file($_FILES['image']['tmp_name'])) {
        $message = 'Invalid upload.';
        $messageType = 'error';
    } else {
        $allowedTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        ];

        $imageInfo = @getimagesize($_FILES['image']['tmp_name']);
        $mimeType = $imageInfo['mime'] ?? '';

        if ($imageInfo === false || !isset($allowedTypes[$mimeType])) {
            $message = 'Only JPG, PNG, GIF, and WebP images are allowed.';
            $messageType = 'error';
        } else {
            $uploadDirectory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads';

            if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true)) {
                $message = 'The upload folder could not be created.';
                $messageType = 'error';
            } else {
                $fileName = bin2hex(random_bytes(16)) . '.' . $allowedTypes[$mimeType];
                $destination = $uploadDirectory . DIRECTORY_SEPARATOR . $fileName;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                    try {
                        ensureImageUploadsTable($conn);

                        $userId = (int) $_SESSION['id'];
                        $uploaderName = (string) ($_SESSION['fullname'] ?? 'Unknown user');
                        $uploaderEmail = (string) ($_SESSION['email'] ?? '');
                        $originalName = substr(basename($_FILES['image']['name']), 0, 255);

                        $stmt = $conn->prepare(
                            'INSERT INTO image_uploads
                                (user_id, uploader_name, uploader_email, stored_filename, original_filename, artwork_title, mime_type)
                             VALUES (?, ?, ?, ?, ?, ?, ?)'
                        );

                        if (!$stmt) {
                            throw new RuntimeException('The photo record could not be created.');
                        }

                        $stmt->bind_param(
                            'issssss',
                            $userId,
                            $uploaderName,
                            $uploaderEmail,
                            $fileName,
                            $originalName,
                            $artworkTitle,
                            $mimeType
                        );

                        if (!$stmt->execute()) {
                            throw new RuntimeException('The photo record could not be saved.');
                        }

                        $stmt->close();
                        $message = 'Image uploaded successfully.';
                        $messageType = 'success';
                        $uploadedImage = '../uploads/' . rawurlencode($fileName);
                        $_SESSION['upload_csrf_token'] = bin2hex(random_bytes(32));
                    } catch (Throwable $exception) {
                        @unlink($destination);
                        error_log($exception->getMessage());
                        $message = 'The image could not be added to the photo gallery.';
                        $messageType = 'error';
                    }
                } else {
                    $message = 'The server could not save the image.';
                    $messageType = 'error';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Image</title>
    <link rel="stylesheet" href="../css/style.css?v=dashboard-sidebar-1">
    
    <script src="upload.js" defer></script>
</head>
<body class="with-sidebar">
    <?php renderDashboardSidebar('upload'); ?>

    <main class="upload-box">
        <h1>Upload an image</h1>
        <p>Choose a JPG, PNG, GIF, or WebP image up to 2 MB.</p>

        <?php if ($message !== ''): ?>
            <p class="message <?php echo htmlspecialchars($messageType); ?>" role="alert">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php endif; ?>

        <form id="upload-form" method="post" enctype="multipart/form-data">
            <input
                type="hidden"
                name="csrf_token"
                value="<?php echo htmlspecialchars($_SESSION['upload_csrf_token']); ?>"
            >

            <label for="artwork_title">Artwork title</label>
            <input
                type="text"
                id="artwork_title"
                name="artwork_title"
                maxlength="255"
                value="<?php echo htmlspecialchars($artworkTitle); ?>"
                required
            >

            <label for="image">Image</label>
            <input
                type="file"
                id="image"
                name="image"
                accept=".jpg,.jpeg,.png,.gif,.webp,image/jpeg,image/png,image/gif,image/webp"
                required
            >

            <p id="client-error" class="message error" role="alert" hidden></p>
            <img id="preview" class="preview" alt="Selected image preview" hidden>

            <button type="submit">Upload</button>
        </form>

        <?php if ($uploadedImage !== ''): ?>
            <div class="result">
                <h2>Uploaded image</h2>
                <img src="<?php echo htmlspecialchars($uploadedImage); ?>" alt="Uploaded image">
            </div>
        <?php endif; ?>

    </main>
</body>
</html>
