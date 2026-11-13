<?php
/*
 * One showcase.   showcase-detail.php?id=3  shows showcase #3.
 * Placeholder page: change its words and picture in $showcases in includes/data.php.
 * Not part of the header menu, so no menu item is underlined.
 */
require_once __DIR__ . '/../includes/init.php';

$id   = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$show = find_by_id($showcases, $id);

if (!$show) {
    http_response_code(404);
}

$pageTitle  = $show ? $show['title'] : 'Showcase not found';
$activePage = '';
require __DIR__ . '/../includes/header.php';
?>

<div class="page-title">
    <h1>Artwork Showcase</h1>
</div>

<?php if ($show): ?>
    <section class="detail detail-wide">
        <div class="media r-53"><?= render_image($show['image'], $show['title']) ?></div>

        <div class="detail-info">
            <p class="by">By : <?= e($show['by']) ?></p>
            <h2><?= e($show['title']) ?></h2>
            <p class="specs">
                Location: <?= e($show['location']) ?><br>
                Dates: <?= e($show['dates']) ?>
            </p>
            <p class="desc"><?= e($show['description']) ?></p>
            <a class="btn" href="showcase.php">Back to showcase</a>
        </div>
    </section>
<?php else: ?>
    <p class="empty-state">We couldn't find that showcase. <a href="showcase.php"><u>Back to the showcase</u></a></p>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
