<?php
/*
 * One showcase.   showcase-detail.php?id=3  shows showcase #3.
 * Every picture on the Artwork showcase page opens this page.
 *
 *  - Not logged in: picture, title, artist, description and the comments (read only).
 *  - Logged in:     the same, plus a "Comment..." box and a Profile button in the header.
 *                   Nobody can be logged in until the database exists (see includes/auth.php).
 *
 * Change the words and picture of a showcase in $showcases in includes/data.php.
 */
require_once __DIR__ . '/../includes/init.php';

$id   = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$show = find_by_id($showcases, $id);

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
                <p class="sv-by">By : <?= e($show['by']) ?></p>
                <p class="sv-desc"><?= e($show['description']) ?></p>
            </div>
        </div>

        <section class="comments" aria-labelledby="comments-title">
            <h2 id="comments-title">Comments</h2>

            <?php foreach ($show['comments'] as $comment): ?>
                <article class="comment">
                    <div class="comment-head">
                        <strong><?= e($comment['name']) ?></strong>
                        <time><?= e($comment['date']) ?></time>
                    </div>
                    <p><?= e($comment['text']) ?></p>
                    <div class="comment-actions">
                        <button class="c-like" type="button" aria-pressed="false" aria-label="Like this comment">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" aria-hidden="true"><path d="M12 20.5s-8-5-8-10.8A4.7 4.7 0 0 1 12 6.6a4.7 4.7 0 0 1 8 3.1c0 5.8-8 10.8-8 10.8z"/></svg>
                        </button>
                        <button class="c-reply" type="button" aria-label="Reply">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" aria-hidden="true"><path d="M4 5h16v11H9l-5 4z"/></svg>
                        </button>
                    </div>
                </article>
            <?php endforeach; ?>

            <?php if (is_logged_in()): ?>
                <!-- Only logged-in visitors see the comment box. Saving the comment needs the database. -->
                <form class="comment-form" method="post" action="showcase-detail.php?id=<?= (int) $show['id'] ?>">
                    <input type="text" name="comment" placeholder="Comment..." aria-label="Write a comment" autocomplete="off">
                </form>
            <?php endif; ?>
        </section>
    </section>
<?php else: ?>
    <p class="empty-state">We couldn't find that showcase. <a href="showcase.php"><u>Back to the showcase</u></a></p>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
