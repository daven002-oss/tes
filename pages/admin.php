<?php
/*
 * Admin Overview - opened from the "Admin Overview" button on an admin's profile page.
 * Placeholder page. Only admins can see it; everyone else is sent away.
 * (Nobody is an admin until the database sets $_SESSION['is_admin'].)
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
    <p>Placeholder - manage artworks, artists, events and users here once the database exists.</p>
</div>

<section class="text-block">
    <h2>Coming soon</h2>
    <p>Add your admin tools to this page.</p>
</section>

<p style="margin-top:20px"><a class="view-all" href="user-profile.php">&larr; Back to my profile</a></p>

<?php require __DIR__ . '/../includes/footer.php'; ?>
