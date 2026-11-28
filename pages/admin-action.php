<?php
/*
 * Handles every button on the Admin Space pages (all are POST forms with a CSRF token).
 * Who may do what is decided HERE, not in the page:
 *   - any admin: delete artworks, review submissions, use the inbox, delete normal users
 *   - only SUPER_ADMIN_EMAIL (admin@vela.test): make users admin / normal, delete other admins
 */
require_once __DIR__ . '/../includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin.php');
}
if (!is_logged_in()) {
    redirect('login.php');
}
if (!is_admin()) {
    http_response_code(403);
    redirect('user-profile.php');
}

// Where to go back to: only our own admin pages.
$back = (string) ($_POST['return'] ?? 'admin.php');
if (!preg_match('/^admin(-[a-z]+)?\.php(\?[A-Za-z0-9=&_%.-]*)?$/', $back)) {
    $back = 'admin.php';
}
if (!csrf_valid($_POST['csrf'] ?? null)) {
    flash_set('Your session expired. Please try again.', 'error');
    redirect($back);
}

$me     = current_user();
$pdo    = db();
$action = (string) ($_POST['action'] ?? '');
$id     = (int) ($_POST['id'] ?? 0);

switch ($action) {

    case 'delete_artwork':
        if (delete_artwork($id)) {
            flash_set('Artwork deleted.');
        } else {
            flash_set('That artwork no longer exists.', 'error');
        }
        break;

    case 'delete_user':
        $stmt = $pdo->prepare('SELECT id, email, is_admin, avatar_path FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $target = $stmt->fetch();
        if (!$target) {
            flash_set('That user no longer exists.', 'error');
        } elseif ((int) $target['id'] === $me['id']) {
            flash_set("You can't delete your own account here.", 'error');
        } elseif (strcasecmp($target['email'], SUPER_ADMIN_EMAIL) === 0) {
            flash_set("The main admin account can't be deleted.", 'error');
        } elseif ($target['is_admin'] && !is_super_admin()) {
            flash_set('Only ' . SUPER_ADMIN_EMAIL . ' can delete another admin.', 'error');
        } else {
            $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);   // their likes, follows and comments go with them
            delete_uploaded_file($target['avatar_path']);
            flash_set('User deleted.');
        }
        break;

    case 'set_admin':
        $make = !empty($_POST['make_admin']);
        $stmt = $pdo->prepare('SELECT id, email FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $target = $stmt->fetch();
        if (!is_super_admin()) {
            flash_set('Only ' . SUPER_ADMIN_EMAIL . ' can change who is an admin.', 'error');
        } elseif (!$target) {
            flash_set('That user no longer exists.', 'error');
        } elseif ((int) $target['id'] === $me['id'] || strcasecmp($target['email'], SUPER_ADMIN_EMAIL) === 0) {
            flash_set("The main admin's role can't be changed.", 'error');
        } else {
            $pdo->prepare('UPDATE users SET is_admin = ? WHERE id = ?')->execute([$make ? 1 : 0, $id]);
            flash_set($make ? 'User is now an admin.' : 'User is now a normal user.');
        }
        break;

    case 'review':
        $decision = (string) ($_POST['decision'] ?? '');
        $newStatus = ['approve' => 'published', 'changes' => 'draft', 'unpublish' => 'draft'][$decision] ?? null;
        if ($newStatus === null) {
            flash_set('Unknown decision.', 'error');
            break;
        }
        $upd = $pdo->prepare('UPDATE artworks SET status = ? WHERE id = ?');
        $upd->execute([$newStatus, $id]);
        $exists = $pdo->prepare('SELECT COUNT(*) FROM artworks WHERE id = ?');
        $exists->execute([$id]);
        if (!$exists->fetchColumn()) {
            flash_set('That artwork no longer exists.', 'error');
        } else {
            flash_set($newStatus === 'published' ? 'Artwork approved and published.' : 'Artwork moved to drafts (changes requested).');
        }
        break;

    case 'msg_read_all':
        $pdo->exec('UPDATE messages SET is_read = 1');
        flash_set('All messages marked as read.');
        break;

    case 'msg_ignore':
        $pdo->prepare('UPDATE messages SET is_ignored = 1, is_read = 1 WHERE id = ?')->execute([$id]);
        flash_set('Message ignored.');
        $back = 'admin-messages.php';
        break;

    case 'msg_reply':
        $body = trim((string) ($_POST['body'] ?? ''));
        $exists = $pdo->prepare('SELECT COUNT(*) FROM messages WHERE id = ?');
        $exists->execute([$id]);
        if (!$exists->fetchColumn()) {
            flash_set('That message no longer exists.', 'error');
            $back = 'admin-messages.php';
        } elseif ($body === '' || mb_strlen($body) > 2000) {
            flash_set('Write a reply (up to 2000 characters).', 'error');
        } else {
            $pdo->prepare('INSERT INTO message_replies (message_id, admin_id, body) VALUES (?, ?, ?)')->execute([$id, $me['id'], $body]);
            $pdo->prepare('UPDATE messages SET is_read = 1 WHERE id = ?')->execute([$id]);
            flash_set('Reply sent.');
        }
        break;

    default:
        flash_set('Unknown action.', 'error');
}

redirect($back);
