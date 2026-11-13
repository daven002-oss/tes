<?php
// "Artwork showcase" page - a grid of showcases (replaces the old Artwork Detail header tab).
// The list comes from $showcases in includes/data.php.
require_once __DIR__ . '/../includes/init.php';
$pageTitle  = 'Artwork Showcase';
$activePage = 'showcase';
require __DIR__ . '/../includes/header.php';
?>

<div class="page-title">
    <h1>Artwork Showcase</h1>
</div>

<section class="grid-3">
    <?php foreach ($showcases as $show): ?>
        <?php showcase_card($show); ?>
    <?php endforeach; ?>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
