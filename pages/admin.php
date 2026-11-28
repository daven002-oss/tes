<?php
/*
 * Admin Space - Overview. Opened from the "Admin Overview" button on an admin's profile.
 * Admins only (admin_top.php sends everyone else away).
 */
$pageTitle   = 'Admin overview';
$adminActive = 'overview';
require __DIR__ . '/../includes/admin_top.php';

$pdo = db();
$count = function (string $sql) use ($pdo): int { return (int) $pdo->query($sql)->fetchColumn(); };

$totalArtworks = $count('SELECT COUNT(*) FROM artworks');
$published     = $count("SELECT COUNT(*) FROM artworks WHERE status = 'published'");
$drafts        = $count("SELECT COUNT(*) FROM artworks WHERE status = 'draft'");
$awaiting      = $count("SELECT COUNT(*) FROM artworks WHERE status = 'in_review'");
$totalUsers    = $count('SELECT COUNT(*) FROM users');
$newUsers      = $count("SELECT COUNT(*) FROM users WHERE created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')");
$totalEvents   = $count('SELECT COUNT(*) FROM events');
$scheduled     = $count('SELECT COUNT(*) FROM event_schedules');

$recent = $pdo->query(ADMIN_ARTWORK_SQL . ' ORDER BY w.created_at DESC, w.id DESC LIMIT 5')->fetchAll();

// Gallery activity: the newest artwork, sign up and message
$activity = [];
if ($row = $pdo->query('SELECT w.title, w.created_at, a.name FROM artworks w JOIN artists a ON a.id = w.artist_id ORDER BY w.created_at DESC, w.id DESC LIMIT 1')->fetch()) {
    $activity[] = ['Artwork added', '&ldquo;' . e($row['title']) . '&rdquo; by ' . e($row['name']), $row['created_at']];
}
if ($row = $pdo->query('SELECT username, created_at FROM users ORDER BY created_at DESC, id DESC LIMIT 1')->fetch()) {
    $activity[] = ['New member', e($row['username']) . ' joined Artspace.', $row['created_at']];
}
if ($row = $pdo->query('SELECT sender_name, subject, created_at FROM messages WHERE is_ignored = 0 ORDER BY created_at DESC LIMIT 1')->fetch()) {
    $activity[] = ['New message', e($row['sender_name']) . ': ' . e($row['subject']), $row['created_at']];
}
?>

<h1 class="admin-title">Admin overview</h1>

<section class="stat-row stat-4">
    <div class="stat"><span>Total artworks</span><strong><?= $totalArtworks ?></strong><small><?= $published ?> published &middot; <?= $drafts ?> <?= $drafts === 1 ? 'draft' : 'drafts' ?></small></div>
    <div class="stat"><span>Total users</span><strong><?= $totalUsers ?></strong><small><?= $newUsers ?> new this month</small></div>
    <div class="stat"><span>Artspace Events</span><strong><?= $totalEvents ?></strong><small><?= $scheduled ?> scheduled by members</small></div>
    <div class="stat"><span>Awaiting review</span><strong><?= $awaiting ?></strong><small>Ready for your approval</small></div>
</section>

<section class="admin-card">
    <div class="admin-card-head">
        <div>
            <h2>Recent artworks</h2>
            <p>Manage the latest additions to your gallery.</p>
        </div>
        <a class="btn-outline-sm" href="admin-artworks.php">All artworks &rarr;</a>
    </div>
    <?php admin_artwork_table($recent, 'admin.php'); ?>
    <p class="table-foot">Showing <?= count($recent) ?> of <?= $totalArtworks ?> artworks</p>
</section>

<div class="admin-two">
    <section class="admin-card">
        <h2>Gallery activity</h2>
        <p class="muted-sm">Latest updates from your workspace.</p>
        <ul class="activity">
            <?php foreach ($activity as [$what, $text, $when]): ?>
                <li><strong><?= e($what) ?></strong><span><?= $text ?></span><time><?= e(time_ago($when)) ?></time></li>
            <?php endforeach; ?>
        </ul>
    </section>
    <section class="admin-card admin-dark">
        <h2>Gallery View</h2>
        <p><?= $awaiting ?> artwork<?= $awaiting === 1 ? ' is' : 's are' ?> waiting for review.<br>Check the details before publishing.</p>
        <a class="btn-white" href="admin-review.php">Review submissions &rarr;</a>
    </section>
</div>

<?php require __DIR__ . '/../includes/admin_bottom.php'; ?>
