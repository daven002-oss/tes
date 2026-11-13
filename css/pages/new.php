<?php
// "New" page - newly released artworks (the old Gallery page).
// Which artworks appear here is set by the 'new' section in includes/data.php.
require_once __DIR__ . '/../includes/init.php';
$pageTitle  = 'New';
$activePage = 'new';
require __DIR__ . '/../includes/header.php';

$newArtworks = array_filter($artworks, function ($a) {
    return $a['section'] === 'new';
});
?>

<div class="page-title">
    <h1 class="upper">New!!</h1>
</div>

<section class="grid-3">
    <?php foreach ($newArtworks as $art): ?>
        <?php art_card($art); ?>
    <?php endforeach; ?>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
