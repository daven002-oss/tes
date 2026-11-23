<?php
/*
 * One showcase.   showcase-detail.php?id=3  shows showcase #3.
 * Every picture on the Artwork showcase page opens this page.
 *
 *  - Not logged in: picture, title, artist, description and the comments (read only).
 *  - Logged in:     the same, plus a "Comment..." box (comments are saved in the database).
 *
 * Change the words and picture of a showcase in $showcases in includes/data.php.
 */
require_once __DIR__ . '/../includes/init.php';

$id   = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$show = find_by_id($showcases, $id);
if ($show) {
    handle_comment_post('showcase', (int) $show['id']);   // saves a new comment, if one was just sent
}

if (!$show) {
    http_response_code(404);
}

$pageTitle  = $show ? $show['title'] : 'Showcase not found';
$activePage = 'showcase';   // "Artwork showcase" stays underlined in the header
require __DIR__ . '/../includes/header.php';
?>

<?php if ($show): ?>
    <section class="showcase-view">
        <div class="sv-top">
            <div class="media r-53 sv-image"><?= render_image($show['image'], $show['title']) ?></div>

            <div class="sv-info">
                <h1><?= e($show['title']) ?></h1>
                <p class="sv-by">By : <a href="artist-profile.php?id=<?= (int) $show['artist_id'] ?>"><?= e($show['by']) ?></a></p>
                <p class="sv-desc"><?= e($show['description']) ?></p>
            </div>
        </div>

        <?php
        $comments      = get_comments('showcase', (int) $show['id']);
        $commentAction = 'showcase-detail.php?id=' . (int) $show['id'];
        require __DIR__ . '/../includes/comments.php';
        ?>
    </section>
<?php else: ?>
    <p class="empty-state">We couldn't find that showcase. <a href="showcase.php"><u>Back to the showcase</u></a></p>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
