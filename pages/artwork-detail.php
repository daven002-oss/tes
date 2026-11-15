<?php
/*
 * Artwork detail page.
 *   artwork-detail.php        -> shows artwork #1 (this is what the header link opens)
 *   artwork-detail.php?id=5   -> shows artwork #5
 * To change the words or picture of an artwork, edit it in includes/data.php.
 */
require_once __DIR__ . '/../includes/init.php';

$id  = isset($_GET['id']) ? (int) $_GET['id'] : 1;
$art = find_by_id($artworks, $id);

if (!$art) {
    http_response_code(404);
}

$pageTitle  = $art ? $art['title'] : 'Artwork not found';
$activePage = '';   // not in the header menu any more, so nothing is underlined
require __DIR__ . '/../includes/header.php';
?>

<div class="page-title">
    <h1>Artwork</h1>
</div>

<?php if ($art): ?>
    <section class="detail">
        <div class="media r-34"><?= render_image($art['image'], $art['title']) ?></div>

        <div class="detail-info">
            <p class="by">
                <?php if ($art['artist_id']): ?>
                    <a href="artist-profile.php?id=<?= (int) $art['artist_id'] ?>"><?= e($art['artist']) ?></a>
                <?php else: ?>
                    <?= e($art['artist']) ?>
                <?php endif; ?>
            </p>
            <h2><?= e($art['title']) ?></h2>
            <p class="specs">
                Size: <?= e($art['size']) ?><br>
                Medium: <?= e($art['medium']) ?><br>
                Year: <?= e($art['year']) ?><br>
                Location: <?= e($art['location']) ?>
            </p>
            <p class="desc"><?= e($art['description']) ?></p>
            <p class="price"><?= e($art['price']) ?></p>
            <button class="btn" type="button">Contact owner</button>
        </div>
    </section>

    <?php
    // Every artwork gets the placeholder comments unless it has its own 'comments' list in data.php.
    $comments      = $art['comments'] ?? $defaultComments;
    $commentAction = 'artwork-detail.php?id=' . (int) $art['id'];
    require __DIR__ . '/../includes/comments.php';
    ?>
<?php else: ?>
    <p class="empty-state">We couldn't find that artwork. <a href="new.php"><u>Back to New</u></a></p>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
