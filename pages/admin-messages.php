<?php
/* Admin Space - Messages: the inbox. ?m=ID opens one conversation. */
$pageTitle   = 'Messages';
$adminActive = 'messages';
require __DIR__ . '/../includes/admin_top.php';

$pdo  = db();
$list = $pdo->query('SELECT id, sender_name, subject, body, is_read, created_at FROM messages WHERE is_ignored = 0 ORDER BY created_at DESC, id DESC')->fetchAll();

$openId = (int) ($_GET['m'] ?? 0);
$open   = null;
foreach ($list as $m) {
    if ((int) $m['id'] === $openId || ($openId === 0 && $open === null)) {
        $open = $m;
    }
}
$replies = [];
$related = null;
if ($open) {
    if (!$open['is_read']) {                                   // opening it marks it as read
        $pdo->prepare('UPDATE messages SET is_read = 1 WHERE id = ?')->execute([$open['id']]);
        $open['is_read'] = 1;
        foreach ($list as &$m) { if ($m['id'] === $open['id']) { $m['is_read'] = 1; } } unset($m);
        $unreadCount = max(0, $unreadCount - 1);
    }
    $r = $pdo->prepare('SELECT body, created_at FROM message_replies WHERE message_id = ? ORDER BY created_at, id');
    $r->execute([$open['id']]);
    $replies = $r->fetchAll();

    $rel = $pdo->prepare(
        "SELECT w.id, w.title, w.status, a.name AS artist_name, pic.path AS image
           FROM messages ms JOIN artworks w ON w.id = ms.artwork_id JOIN artists a ON a.id = w.artist_id
           LEFT JOIN pictures pic ON pic.owner_type = 'artwork' AND pic.owner_id = w.id
          WHERE ms.id = ?");
    $rel->execute([$open['id']]);
    $related = $rel->fetch() ?: null;
}
$here = 'admin-messages.php' . ($open ? '?m=' . (int) $open['id'] : '');
?>

<div class="admin-title-row">
    <div>
        <h1 class="admin-title">Messages</h1>
        <p class="muted-sm">Stay connected with your artists and gallery community.</p>
    </div>
    <form method="post" action="admin-action.php">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="msg_read_all"><input type="hidden" name="return" value="<?= e($here) ?>">
        <button class="btn-outline-sm" type="submit">Mark all as read</button>
    </form>
</div>

<section class="admin-card inbox">
    <aside class="inbox-list">
        <h2>Inbox</h2>
        <input class="inbox-search" type="search" placeholder="Search messages" aria-label="Search messages" data-inbox-search>
        <p class="inbox-count">All messages (<?= count($list) ?>)</p>
        <?php foreach ($list as $m): ?>
            <a class="inbox-item<?= $open && (int) $m['id'] === (int) $open['id'] ? ' on' : '' ?>" href="admin-messages.php?m=<?= (int) $m['id'] ?>" data-text="<?= e(strtolower($m['sender_name'] . ' ' . $m['subject'] . ' ' . $m['body'])) ?>">
                <span class="inbox-top"><strong><?= e($m['sender_name']) ?></strong><time><?= e(date('d M', strtotime($m['created_at']))) ?></time></span>
                <span class="inbox-subject"><?= e($m['subject']) ?><?= $m['is_read'] ? '' : ' <i class="dot" aria-label="unread"></i>' ?></span>
                <span class="inbox-preview"><?= e(mb_strimwidth($m['body'], 0, 60, '...')) ?></span>
            </a>
        <?php endforeach; ?>
        <?php if (!$list): ?><p class="muted-sm">No messages.</p><?php endif; ?>
    </aside>

    <div class="inbox-view">
    <?php if ($open): ?>
        <div class="inbox-head">
            <div><h2><?= e($open['sender_name']) ?></h2><p class="muted-sm"><?= e($open['subject']) ?></p></div>
            <form method="post" action="admin-action.php" data-confirm="Ignore this message? It will be removed from the inbox." data-confirm-yes="Yes, ignore" data-confirm-title="Ignore message?">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="msg_ignore"><input type="hidden" name="id" value="<?= (int) $open['id'] ?>"><input type="hidden" name="return" value="admin-messages.php">
                <button class="btn-outline-sm" type="submit">Ignore</button>
            </form>
        </div>
        <div class="thread">
            <p class="thread-date"><?= e(strtoupper(date('d F Y', strtotime($open['created_at'])))) ?></p>
            <div class="bubble in">
                <strong><?= e($open['sender_name']) ?></strong>
                <p><?= nl2br(e($open['body'])) ?></p>
                <time><?= e(date('H:i', strtotime($open['created_at']))) ?></time>
            </div>
            <?php if ($related): ?>
                <div class="related">
                    <span class="thumb"><?= render_image((string) $related['image'], $related['title']) ?></span>
                    <div><small>RELATED ARTWORK</small><strong><?= e($related['title']) ?></strong><span><?= e($related['artist_name']) ?> &middot; <?= e(status_label($related['status'])) ?></span></div>
                    <?php if ($related['status'] === 'published'): ?><a class="link-small" href="artwork-detail.php?id=<?= (int) $related['id'] ?>">View artwork &rarr;</a><?php endif; ?>
                </div>
            <?php endif; ?>
            <?php foreach ($replies as $rp): ?>
                <div class="bubble out">
                    <strong><?= e($adminUser['name']) ?></strong>
                    <p><?= nl2br(e($rp['body'])) ?></p>
                    <time><?= e(date('H:i', strtotime($rp['created_at']))) ?> &middot; Sent</time>
                </div>
            <?php endforeach; ?>
        </div>
        <form class="reply-form" method="post" action="admin-action.php">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="msg_reply"><input type="hidden" name="id" value="<?= (int) $open['id'] ?>"><input type="hidden" name="return" value="<?= e($here) ?>">
            <textarea name="body" rows="3" maxlength="2000" placeholder="Write a reply..." aria-label="Write a reply" required></textarea>
            <button class="btn-black-sm" type="submit">Send reply &rarr;</button>
        </form>
    <?php else: ?>
        <p class="empty-state">Select a message.</p>
    <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/../includes/admin_bottom.php'; ?>
