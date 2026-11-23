<?php
/*
 * Comments section - shared by the artwork page and the showcase page.
 *
 * Before including this file, set:
 *   $comments      - list of comments (name, date, text)
 *   $commentAction - the page address the comment box posts to
 *
 * Not logged in: the comments are read only.
 * Logged in:     a "Comment..." box appears too (comments are saved in the `comments` table).
 */
?>
<section class="comments" aria-labelledby="comments-title">
    <h2 id="comments-title">Comments</h2>

    <?php if (!$comments): ?>
        <p class="no-comments">No comments yet.</p>
    <?php endif; ?>

    <?php foreach ($comments as $comment): ?>
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
        <!-- Only logged-in visitors see the comment box. Press Enter to post. -->
        <form class="comment-form" method="post" action="<?= e($commentAction) ?>">
            <?= csrf_field() ?>
            <input type="text" name="comment" placeholder="Comment..." aria-label="Write a comment" maxlength="1000" autocomplete="off" required>
        </form>
    <?php else: ?>
        <p class="comment-login"><a href="login.php"><u>Log in</u></a> to leave a comment.</p>
    <?php endif; ?>
</section>
