<?php
header('Cache-Control: no-cache, no-store, must-revalidate');
session_start();

if (!isset($_SESSION['id'])) {
    header('Location: ../auth/login.php');
    exit;
}
if (!in_array($_SESSION['role'] ?? '', ['artist', 'admin'], true)) {
    http_response_code(403);
    exit('Only artists and administrators may upload artworks.');
}

require_once '../config/database.php';
require_once '../config/image_uploads.php';
require_once 'dashboard_sidebar.php';

$message = '';
$artworkTitle = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $artworkTitle = trim($_POST['artwork_title'] ?? '');
    $image = $_FILES['image'] ?? null;

    if ($artworkTitle === '' || strlen($artworkTitle) > 255) {
        $message = 'Please enter a title for your artwork (up to 255 characters).';
    } elseif (!$image || $image['error'] !== UPLOAD_ERR_OK) {
        $message = 'Please choose an image to upload.';
    } elseif ($image['size'] > 2 * 1024 * 1024) {
        $message = 'The image must be 2 MB or smaller.';
    } else {
        // Check what kind of image was uploaded, then choose a safe extension.
        $types = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp'
        ];
        $imageInfo = @getimagesize($image['tmp_name']);
        $type = $imageInfo['mime'] ?? '';

        if (!isset($types[$type])) {
            $message = 'Only JPG, PNG, GIF, and WebP images are allowed.';
        } else {
            $folder = '../uploads/';
            if (!is_dir($folder)) {
                mkdir($folder, 0755, true);
            }
            $fileName = uniqid('art_', true) . '.' . $types[$type];

            // Move uploaded image from PHP's temporary folder to uploads/.
            if (!move_uploaded_file($image['tmp_name'], $folder . $fileName)) {
                $message = 'Unable to save the image.';
            } else {
                try {
                    ensureImageUploadsTable($conn);
                    $userId = (int) $_SESSION['id'];
                    $name = $_SESSION['fullname'];
                    $email = $_SESSION['email'];
                    $original = substr(basename($image['name']), 0, 255);

                    // CREATE: Save the artwork details in MySQL.
                    $stmt = $conn->prepare(
                        'INSERT INTO image_uploads (user_id, uploader_name, uploader_email, stored_filename, original_filename, artwork_title, mime_type)
                         VALUES (?, ?, ?, ?, ?, ?, ?)'
                    );
                    $stmt->bind_param('issssss', $userId, $name, $email, $fileName, $original, $artworkTitle, $type);
                    $stmt->execute();
                    $stmt->close();

                    $_SESSION['success'] = 'Image uploaded successfully!';
                    header('Location: artwork_feed.php');
                    exit;
                } catch (Throwable $error) {
                    unlink($folder . $fileName); // Remove the file if saving to MySQL fails.
                    error_log($error->getMessage());
                    $message = 'Could not save the artwork details.';
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
    <link rel="stylesheet" href="../css/style.css?v=responsive-2">
    
    <script src="upload.js" defer></script>
</head>
<body class="with-sidebar">
    <?php renderDashboardSidebar('upload'); ?>

    <main class="upload-box">
        <h1>Upload an image</h1>
        <p>Choose a JPG, PNG, GIF, or WebP image up to 2 MB.</p>

        <?php if ($message !== ''): ?>
            <p class="message error" role="alert"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form id="upload-form" method="post" enctype="multipart/form-data">

            <label for="artwork_title">Artwork title</label>
            <input
                type="text"
                id="artwork_title"
                name="artwork_title"
                maxlength="255"
                value="<?php echo htmlspecialchars($artworkTitle); ?>"
                required
                oninvalid="this.setCustomValidity('Please enter a title for the artwork.')"
                oninput="this.setCustomValidity('')"
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

    </main>
</body>
</html>
