<?php
/*
 * Admin Overview - opened from the "Admin Overview" button on an admin's profile page.
 * Placeholder page. Only admins can see it; everyone else is sent away.
 * (Admins are the users with is_admin = 1 in the `users` table.)
 */
require_once __DIR__ . '/../includes/init.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}
if (!is_admin()) {
    header('Location: user-profile.php');
    exit;
}

$pageTitle  = 'Admin Overview';
$activePage = '';
require __DIR__ . '/../includes/header.php';
?>

<div class="page-title">
    <h1>Admin Overview</h1>
    <p>A quick look at what is in the database. Add or change content in phpMyAdmin.</p>
</div>

<?php
$counts = [];
foreach (['users' => 'Users', 'artists' => 'Artists', 'artworks' => 'Artworks', 'showcases' => 'Showcases', 'events' => 'Events',
          'artwork_likes' => 'Artworks saved (hearts)', 'artist_follows' => 'Artist follows', 'event_schedules' => 'Events scheduled', 'comments' => 'Comments'] as $table => $label) {
    $counts[$label] = (int) db()->query("SELECT COUNT(*) FROM $table")->fetchColumn();
}
?>
<section class="text-block">
    <h2>Totals</h2>
    <ul class="result-list">
        <?php foreach ($counts as $label => $n): ?>
            <li><strong><?= $n ?></strong> &nbsp;<?= e($label) ?></li>
        <?php endforeach; ?>
    </ul>
</section>

<p style="margin-top:20px"><a class="view-all" href="user-profile.php">&larr; Back to my profile</a></p>

<?php require __DIR__ . '/../includes/footer.php'; ?>
