<?php
/*
 * Top of every Admin Space page: top bar + left menu. Closed by includes/admin_bottom.php.
 * Before including, set:  $pageTitle  and  $adminActive ('overview' | 'users' | 'review' | 'messages')
 */
require_once __DIR__ . '/init.php';

if (!is_logged_in()) {
    redirect('login.php');
}
if (!is_admin()) {
    redirect('user-profile.php');
}

$adminLayout = true;
$adminUser   = current_user();
$unreadCount = (int) db()->query('SELECT COUNT(*) FROM messages WHERE is_read = 0 AND is_ignored = 0')->fetchColumn();
$logoFile    = dirname(__DIR__) . '/' . $logoImage;

$adminNav = [
    'overview' => ['Overview',           'admin.php'],
    'users'    => ['Users',              'admin-users.php'],
    'review'   => ['Review Submissions', 'admin-review.php'],
    'messages' => ['Messages',           'admin-messages.php'],
];

/*
 * The artwork table used on Overview and All artworks. Each row has a red Delete button
 * that asks "are you sure?" first. $return = the admin page to come back to.
 */
function admin_artwork_table(array $rows, string $return): void
{ ?>
    <div class="table-wrap">
        <table class="admin-table">
            <thead><tr><th>Artwork</th><th>Artist</th><th>Status</th><th>Added</th><th class="col-action">Action</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td>
                        <div class="cell-art">
                            <span class="thumb"><?= render_image((string) $r['image'], $r['title']) ?></span>
                            <?php if ($r['status'] === 'published'): ?>
                                <a href="artwork-detail.php?id=<?= (int) $r['id'] ?>"><strong><?= e($r['title']) ?></strong></a>
                            <?php else: ?>
                                <strong><?= e($r['title']) ?></strong>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td><?= e($r['artist_name']) ?></td>
                    <td><span class="pill pill-<?= e($r['status']) ?>"><?= e(status_label($r['status'])) ?></span></td>
                    <td><?= e(date('d M Y', strtotime($r['created_at']))) ?></td>
                    <td class="col-action">
                        <form method="post" action="admin-action.php" data-confirm="Delete &ldquo;<?= e($r['title']) ?>&rdquo;? It will also disappear from everyone's saved artworks. This can't be undone.">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete_artwork">
                            <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                            <input type="hidden" name="return" value="<?= e($return) ?>">
                            <button class="link-delete" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?>
                <tr><td colspan="5" class="table-empty">No artworks found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php }

const ADMIN_ARTWORK_SQL =
    "SELECT w.id, w.title, w.status, w.created_at, a.name AS artist_name, pic.path AS image
       FROM artworks w
       JOIN artists a ON a.id = w.artist_id
       LEFT JOIN pictures pic ON pic.owner_type = 'artwork' AND pic.owner_id = w.id";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($pageTitle) ?> | Admin Space</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poiret+One&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="admin-body">

<header class="admin-top">
    <a class="logo" href="admin.php" aria-label="Admin overview">
        <?php if (is_file($logoFile)): ?>
            <img src="../<?= e($logoImage) ?>" alt="<?= e($siteName) ?> logo">
        <?php else: ?>
            <span class="logo-placeholder">LOGO</span>
        <?php endif; ?>
    </a>
    <span class="admin-brand">ADMIN SPACE</span>
    <nav class="admin-top-actions" aria-label="Admin shortcuts">
        <a class="admin-link" href="home.php">View website</a>
        <a class="admin-pill" href="user-profile.php"><?= e($adminUser['name']) ?></a>
    </nav>
</header>

<div class="admin-shell">
    <aside class="admin-side">
        <p class="admin-side-label">WORKSPACE</p>
        <nav aria-label="Admin menu">
            <?php foreach ($adminNav as $key => [$label, $href]): ?>
                <a class="admin-nav<?= $adminActive === $key ? ' active' : '' ?>" href="<?= e($href) ?>"<?= $adminActive === $key ? ' aria-current="page"' : '' ?>>
                    <span><?= e($label) ?></span>
                    <?php if ($key === 'messages' && $unreadCount > 0): ?><b class="admin-badge"><?= $unreadCount ?></b><?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <form class="admin-logout" method="post" action="logout.php">
            <?= csrf_field() ?>
            <button type="submit">Log out</button>
        </form>
    </aside>

    <main class="admin-main">
        <?= flash_html() ?>
