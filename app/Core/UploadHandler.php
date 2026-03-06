<?php
declare(strict_types=1);
namespace App\Core;

use RuntimeException;

class UploadHandler
{
    /**
     * Handle an image file upload from a $_FILES entry.
     *
     * @param array  $fileInput  The entry from $_FILES, e.g. $_FILES['image'].
     * @param string $subdir     Subdirectory under UPLOAD_PATH, e.g. 'banners'.
     * @return string|null       Relative path stored in DB (e.g. 'banners/abc123.jpg'),
     *                           or null if no file was submitted.
     * @throws RuntimeException  On validation or filesystem failure.
     */
    public static function uploadImage(array $fileInput, string $subdir): ?string
    {
        // 1. No file submitted — silently return null.
        if (($fileInput['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        // 2. Check for upload errors.
        if ($fileInput['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException(
                'File upload failed with error code ' . $fileInput['error'] . '.'
            );
        }

        // 3. Validate file size.
        if ($fileInput['size'] > UPLOAD_MAX_SIZE) {
            $maxMb = round(UPLOAD_MAX_SIZE / 1024 / 1024, 1);
            throw new RuntimeException(
                "File exceeds the maximum allowed size of {$maxMb} MB."
            );
        }

        // 4. Validate real MIME type via finfo (never trust the browser-supplied MIME).
        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $fileInput['tmp_name']);
        finfo_close($finfo);

        if ($mimeType === false || !in_array($mimeType, ALLOWED_IMAGE_MIMES, true)) {
            throw new RuntimeException(
                'Invalid file type. Only JPEG, PNG, GIF and WebP images are accepted.'
            );
        }

        // 5. Validate extension.
        $ext = strtolower((string) pathinfo((string) $fileInput['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, ALLOWED_IMAGE_EXTS, true)) {
            throw new RuntimeException(
                'Invalid file extension. Allowed: ' . implode(', ', ALLOWED_IMAGE_EXTS) . '.'
            );
        }

        // 6. Generate a cryptographically safe filename.
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;

        // 7. Build the destination path and ensure the directory exists.
        $destDir = rtrim(UPLOAD_PATH, '/') . '/' . $subdir;

        if (!is_dir($destDir) && !mkdir($destDir, 0755, true) && !is_dir($destDir)) {
            throw new RuntimeException(
                "Unable to create upload directory: {$destDir}"
            );
        }

        $destPath = $destDir . '/' . $filename;

        // 8. Move the uploaded file.
        if (!move_uploaded_file($fileInput['tmp_name'], $destPath)) {
            throw new RuntimeException(
                'Failed to move uploaded file to destination.'
            );
        }

        // 9. Return the relative path for DB storage.
        return $subdir . '/' . $filename;
    }

    /**
     * Safely delete a previously uploaded file.
     *
     * @param string|null $path  Relative path as stored in DB (e.g. 'banners/abc123.jpg').
     */
    public static function delete(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        $uploadRoot = rtrim(UPLOAD_PATH, '/');
        $fullPath   = $uploadRoot . '/' . ltrim($path, '/');

        // Use realpath() to prevent path-traversal outside UPLOAD_PATH.
        $real = realpath($fullPath);

        if ($real === false) {
            // File does not exist — nothing to delete.
            return;
        }

        $realRoot = realpath($uploadRoot);

        if ($realRoot === false || strncmp($real, $realRoot . '/', strlen($realRoot) + 1) !== 0) {
            // Resolved path is outside the upload root — refuse to delete.
            return;
        }

        if (is_file($real)) {
            unlink($real);
        }
    }
}
