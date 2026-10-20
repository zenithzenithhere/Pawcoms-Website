<?php

function ensureImageUploadsTable(mysqli $conn): void
{
    $sql = "
        CREATE TABLE IF NOT EXISTS image_uploads (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            uploader_name VARCHAR(255) NOT NULL,
            uploader_email VARCHAR(255) NOT NULL,
            stored_filename VARCHAR(255) NOT NULL UNIQUE,
            original_filename VARCHAR(255) NOT NULL,
            artwork_title VARCHAR(255) NOT NULL DEFAULT '',
            mime_type VARCHAR(50) NOT NULL,
            is_pinned TINYINT(1) NOT NULL DEFAULT 0,
            uploaded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_image_uploads_user (user_id),
            INDEX idx_image_uploads_date (uploaded_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";

    if (!$conn->query($sql)) {
        throw new RuntimeException('The photo records could not be prepared.');
    }

    $columnResult = $conn->query("SHOW COLUMNS FROM image_uploads LIKE 'artwork_title'");

    if (!$columnResult) {
        throw new RuntimeException('The artwork title field could not be checked.');
    }

    if ($columnResult->num_rows === 0) {
        if (!$conn->query("ALTER TABLE image_uploads ADD artwork_title VARCHAR(255) NOT NULL DEFAULT '' AFTER original_filename")) {
            throw new RuntimeException('The artwork title field could not be added.');
        }

        if (!$conn->query('UPDATE image_uploads SET artwork_title = original_filename')) {
            throw new RuntimeException('Existing artwork titles could not be prepared.');
        }
    }
}
