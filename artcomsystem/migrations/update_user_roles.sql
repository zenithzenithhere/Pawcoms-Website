-- Run against the existing application database.
-- Existing User, Student, and empty roles become Commissioners.
ALTER TABLE users
MODIFY role ENUM('admin', 'user', 'student', 'artist', 'commissioner')
COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'commissioner';

UPDATE users
SET role = 'commissioner'
WHERE role IN ('user', 'student', '');

ALTER TABLE users
MODIFY role ENUM('admin', 'artist', 'commissioner')
COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'commissioner';
