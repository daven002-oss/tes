<?php
/*
 * Handles the forms on "My profile": Edit profile (username + picture) and posting / deleting your own artworks.
 * Every request is a POST with a CSRF token from a logged-in user.
 */
require_once __DIR__ . '/../includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('user-profile.php');
}
if (!is_logged_in()) {
    redirect('login.php');
}

$me   = current_user();
$pdo  = db();
$back = 'user-profile.php';

if (!csrf_valid($_POST['csrf'] ?? null)) {
    flash_set('Your session expired (or the picture was too big for the server). Please try again.', 'error');
    redirect($back);
}

$action = (string) ($_POST['action'] ?? '');

/* ---------- Edit profile: username + profile picture ---------- */
if ($action === 'update_profile') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $newAvatar = null;

    if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) {
        flash_set('Username must be 3-30 characters: letters, numbers or underscore.', 'error');
        redirect($back);
    }
    $taken = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ? AND id <> ?');
    $taken->execute([$username, $me['id']]);
    if ($taken->fetchColumn()) {
        flash_set('That username is already taken.', 'error');
        redirect($back);
    }

    if (isset($_FILES['avatar']) && ($_FILES['avatar']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $saved = save_uploaded_image($_FILES['avatar'], 'avatars');
        if (is_string($saved)) {
            flash_set($saved, 'error');
            redirect($back);
        }
        $newAvatar = $saved['path'];
    }

    // The name shown on the profile follows the username if it was the same before.
    $displayName = $me['name'] === $me['username'] ? $username : $me['name'];

    try {
        $pdo->beginTransaction();
        $pdo->prepare('UPDATE users SET username = ?, display_name = ?, avatar_path = COALESCE(?, avatar_path) WHERE id = ?')
            ->execute([$username, $displayName, $newAvatar, $me['id']]);
        if ($me['artist_id'] !== null && $displayName !== $me['name']) {      // keep their artist name in step
            $pdo->prepare('UPDATE artists SET name = ? WHERE id = ? AND name = ?')->execute([$displayName, $me['artist_id'], $me['name']]);
        }
        $pdo->commit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        delete_uploaded_file($newAvatar);
        flash_set('Could not save: that name is already used by an artist or user.', 'error');
        redirect($back);
    }
    if ($newAvatar !== null) {
        delete_uploaded_file($me['avatar']);                                   // remove the old uploaded picture
    }
    flash_set('Profile updated.');
    redirect($back);
}

/* ---------- Post your own artwork ---------- */
if ($action === 'post_artwork') {
    $back  = 'user-profile.php?tab=artworks';
    $title = trim((string) ($_POST['title'] ?? ''));
    $fields = [
        'medium'      => [trim((string) ($_POST['medium'] ?? '')), 60],
        'size'        => [trim((string) ($_POST['size'] ?? '')), 40],
        'year'        => [trim((string) ($_POST['year'] ?? '')), 30],
        'description' => [trim((string) ($_POST['description'] ?? '')), 2000],
    ];
    if ($title === '' || mb_strlen($title) > 120) {
        flash_set('Please give your artwork a title (up to 120 characters).', 'error');
        redirect($back);
    }
    foreach ($fields as $label => [$value, $max]) {
        if (mb_strlen($value) > $max) {
            flash_set('The ' . $label . ' is too long (max ' . $max . ' characters).', 'error');
            redirect($back);
        }
    }

    $saved = save_uploaded_image($_FILES['picture'] ?? [], 'artworks');
    if (is_string($saved)) {
        flash_set($saved, 'error');
        redirect($back);
    }

    // The same picture file can only be used once (checked here for a clear message; the database enforces it too).
    $dup = $pdo->prepare('SELECT COUNT(*) FROM pictures WHERE file_hash = ?');
    $dup->execute([$saved['hash']]);
    if ($dup->fetchColumn()) {
        delete_uploaded_file($saved['path']);
        flash_set('That picture is already on Artspace. Every picture can only be used once.', 'error');
        redirect($back);
    }

    try {
        $pdo->beginTransaction();

        // Every artwork must have an artist: make this person's artist profile the first time they post.
        $artistId = $me['artist_id'];
        if ($artistId === null) {
            $name = $me['name'];
            $exists = $pdo->prepare('SELECT COUNT(*) FROM artists WHERE name = ?');
            $exists->execute([$name]);
            if ($exists->fetchColumn()) {
                $name = $me['name'] . ' (@' . $me['username'] . ')';
            }
            $pdo->prepare('INSERT INTO artists (name, joined_on, is_featured) VALUES (?, CURDATE(), 0)')->execute([$name]);
            $artistId = (int) $pdo->lastInsertId();
            $pdo->prepare('UPDATE users SET artist_id = ? WHERE id = ?')->execute([$artistId, $me['id']]);
        }

        // Admins publish straight away; everyone else waits for an admin to approve it.
        $status = $me['is_admin'] ? 'published' : 'in_review';
        $pdo->prepare(
            'INSERT INTO artworks (title, artist_id, placement, size, medium, year_text, description, status, submitted_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        )->execute([$title, $artistId, 'new', $fields['size'][0], $fields['medium'][0], $fields['year'][0], $fields['description'][0], $status, $me['id']]);
        $artworkId = (int) $pdo->lastInsertId();

        // The picture row: its path and its file hash are UNIQUE, so the same picture can't be used twice.
        $pdo->prepare("INSERT INTO pictures (path, owner_type, owner_id, file_hash) VALUES (?, 'artwork', ?, ?)")
            ->execute([$saved['path'], $artworkId, $saved['hash']]);
        $pdo->commit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        delete_uploaded_file($saved['path']);
        $duplicate = $e->getCode() === '23000' && strpos($e->getMessage(), 'uq_pictures_hash') !== false;
        flash_set($duplicate ? 'That picture is already on Artspace. Every picture can only be used once.' : 'Could not post the artwork. Please try again.', 'error');
        redirect($back);
    }
    flash_set($status === 'published' ? 'Your artwork is published on the New page.' : 'Artwork posted! An admin will review it before it appears on the website.');
    redirect($back);
}

/* ---------- Delete / resubmit one of YOUR artworks ---------- */
if ($action === 'delete_my_artwork' || $action === 'resubmit_artwork') {
    $back = 'user-profile.php?tab=artworks';
    $id   = (int) ($_POST['id'] ?? 0);
    $own  = $pdo->prepare('SELECT status FROM artworks WHERE id = ? AND submitted_by = ?');
    $own->execute([$id, $me['id']]);
    $status = $own->fetchColumn();
    if ($status === false) {
        flash_set("That artwork isn't yours (or no longer exists).", 'error');
    } elseif ($action === 'delete_my_artwork') {
        delete_artwork($id);
        flash_set('Artwork deleted.');
    } elseif ($status === 'draft') {
        $pdo->prepare("UPDATE artworks SET status = 'in_review' WHERE id = ?")->execute([$id]);
        flash_set('Sent for review again.');
    }
    redirect($back);
}

flash_set('Unknown action.', 'error');
redirect($back);
