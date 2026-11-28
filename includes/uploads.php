<?php
/*
 * Picture uploads (profile pictures and artworks posted by users).
 * Files are saved in images/uploads/... with a random name, so two uploads never share a file name.
 */
const UPLOAD_MAX_BYTES = 5 * 1024 * 1024;   // 5 MB

/*
 * Checks and saves one uploaded picture ($_FILES['x']).
 * $folder: 'avatars' or 'artworks'.
 * Returns ['path' => 'images/uploads/artworks/ab12.jpg', 'hash' => sha256], or a string with the error message.
 */
function save_uploaded_image(array $file, string $folder)
{
    $code = $file['error'] ?? UPLOAD_ERR_NO_FILE;
    if ($code === UPLOAD_ERR_NO_FILE) {
        return 'Please choose a picture.';
    }
    if ($code === UPLOAD_ERR_INI_SIZE || $code === UPLOAD_ERR_FORM_SIZE || ($file['size'] ?? 0) > UPLOAD_MAX_BYTES) {
        return 'That picture is too big (max 5 MB, and your PHP upload_max_filesize must allow it).';
    }
    if ($code !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        return 'The upload failed. Please try again.';
    }

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $info = @getimagesize($file['tmp_name']);
    if (!isset($allowed[$mime]) || !$info || $info[0] > 8000 || $info[1] > 8000) {
        return 'Please upload a JPG, PNG, WEBP or GIF picture.';
    }

    $dir = dirname(__DIR__) . '/images/uploads/' . $folder;
    if (!is_dir($dir) && !@mkdir($dir, 0775, true)) {
        return 'The server could not create the uploads folder.';
    }
    $name = bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
        return 'The server could not save the picture.';
    }
    return [
        'path' => 'images/uploads/' . $folder . '/' . $name,
        'hash' => hash_file('sha256', $dir . '/' . $name),
    ];
}

// Deletes a file that WE saved in images/uploads (never touches the seeded pictures).
function delete_uploaded_file(?string $path): void
{
    if ($path && strpos($path, 'images/uploads/') === 0 && strpos($path, '..') === false) {
        $file = dirname(__DIR__) . '/' . $path;
        if (is_file($file)) {
            @unlink($file);
        }
    }
}
