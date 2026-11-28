<?php
/*
 * Admin Space - Review submissions: look at artworks one by one and approve or send back.
 *   ?tab=awaiting|published|drafts   &i=0,1,2...  (which one of the tab)
 */
$pageTitle   = 'Review submissions';
$adminActive = 'review';
require __DIR__ . '/../includes/admin_top.php';

$pdo = db();
$tabs = [
    'awaiting'  => ['Awaiting review', 'in_review'],
    'published' => ['Published',       'published'],
    'drafts'    => ['Drafts',          'draft'],
];
$tab = isset($tabs[$_GET['tab'] ?? '']) ? $_GET['tab'] : 'awaiting';

$counts = [];
foreach ($tabs as $key => [, $status]) {
    $c = $pdo->prepare('SELECT COUNT(*) FROM artworks WHERE status = ?');
    $c->execute([$status]);
    $counts[$key] = (int) $c->fetchColumn();
}

$total = $counts[$tab];
$i     = $total ? min(max(0, (int) ($_GET['i'] ?? 0)), $total - 1) : 0;
$art   = null;
if ($total) {
    $stmt = $pdo->prepare(
        "SELECT w.*, a.name AS artist_name, pic.path AS image, u.username AS submitter
           FROM artworks w
           JOIN artists a ON a.id = w.artist_id
           LEFT JOIN users u ON u.id = w.submitted_by
           LEFT JOIN pictures pic ON pic.owner_type = 'artwork' AND pic.owner_id = w.id
          WHERE w.status = ?
          ORDER BY w.created_at DESC, w.id DESC
          LIMIT 1 OFFSET " . $i);
    $stmt->execute([$tabs[$tab][1]]);
    $art = $stmt->fetch();
}
$here = 'admin-review.php?' . http_build_query(['tab' => $tab, 'i' => $i]);
$url  = function (string $t, int $n) { return e('admin-review.php?' . http_build_query(['tab' => $t, 'i' => $n])); };
?>

<div class="admin-title-row">
    <div>
        <h1 class="admin-title">Review submissions</h1>
        <p class="muted-sm">Review artwork details before publishing to the gallery.</p>
    </div>
    <a class="btn-outline-sm" href="admin.php">&larr; Back to overview</a>
</div>

<section class="stat-row stat-3">
    <div class="stat"><span>Awaiting review</span><strong><?= $counts['awaiting'] ?></strong><small>Ready for your approval</small></div>
    <div class="stat"><span>Published artworks</span><strong><?= $counts['published'] ?></strong><small>Visible in the gallery</small></div>
    <div class="stat"><span>Draft artworks</span><strong><?= $counts['drafts'] ?></strong><small>Not yet submitted</small></div>
</section>

<div class="review-tabs">
    <?php foreach ($tabs as $key => [$label]): ?>
        <a class="<?= $tab === $key ? 'on' : '' ?>" href="<?= $url($key, 0) ?>"><?= e($label) ?> (<?= $counts[$key] ?>)</a>
    <?php endforeach; ?>
    <span class="review-count"><?= $total ? 'Submission ' . ($i + 1) . ' of ' . $total : 'Nothing here' ?></span>
</div>

<section class="admin-card review-card">
<?php if ($art): ?>
    <div class="admin-card-head">
        <div><h2>Artwork submission</h2><p>Submitted on <?= e(date('d M Y', strtotime($art['created_at']))) ?><?= $art['submitter'] ? ' by @' . e($art['submitter']) : '' ?></p></div>
        <div class="prev-next">
            <?php if ($i > 0): ?><a class="btn-outline-sm" href="<?= $url($tab, $i - 1) ?>">Previous</a><?php else: ?><span class="btn-outline-sm disabled">Previous</span><?php endif; ?>
            <?php if ($i < $total - 1): ?><a class="btn-outline-sm" href="<?= $url($tab, $i + 1) ?>">Next &rarr;</a><?php else: ?><span class="btn-outline-sm disabled">Next &rarr;</span><?php endif; ?>
        </div>
    </div>
    <div class="review-body">
        <div class="review-photo"><?= render_image((string) $art['image'], $art['title']) ?></div>
        <div class="review-info">
            <span class="pill pill-<?= e($art['status']) ?>"><?= e(status_label($art['status'])) ?></span>
            <h2><?= e($art['title']) ?></h2>
            <p class="by-line">by <?= e($art['artist_name']) ?></p>
            <dl class="review-facts">
                <div><dt>ARTIST</dt><dd><?= e($art['artist_name']) ?></dd></div>
                <div><dt>SUBMITTED</dt><dd><?= e(date('d M Y', strtotime($art['created_at']))) ?></dd></div>
                <div><dt>MEDIUM</dt><dd><?= e($art['medium'] !== '' ? $art['medium'] : '-') ?></dd></div>
                <div><dt>SIZE</dt><dd><?= e($art['size'] !== '' ? $art['size'] : '-') ?></dd></div>
            </dl>
            <?php if ((string) $art['description'] !== ''): ?><p class="review-desc"><?= e($art['description']) ?></p><?php endif; ?>
            <p class="checklist-title">REVIEW CHECKLIST</p>
            <ul class="checklist">
                <li>Artwork image is clear and complete</li>
                <li>Artist name and title are correct</li>
                <li>Content meets the gallery guidelines</li>
            </ul>
            <div class="decision">
                <strong>Your decision</strong>
                <p><?= $art['status'] === 'published' ? 'This artwork is live in the gallery.' : 'Approval will publish this artwork to the gallery.' ?></p>
                <div class="decision-buttons">
                    <?php if ($art['status'] !== 'draft'): ?>
                        <form method="post" action="admin-action.php">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="review"><input type="hidden" name="id" value="<?= (int) $art['id'] ?>">
                            <input type="hidden" name="decision" value="<?= $art['status'] === 'published' ? 'unpublish' : 'changes' ?>"><input type="hidden" name="return" value="<?= e($here) ?>">
                            <button class="btn-outline-lg" type="submit"><?= $art['status'] === 'published' ? 'Unpublish' : 'Request changes' ?></button>
                        </form>
                    <?php endif; ?>
                    <?php if ($art['status'] !== 'published'): ?>
                        <form method="post" action="admin-action.php">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="review"><input type="hidden" name="id" value="<?= (int) $art['id'] ?>">
                            <input type="hidden" name="decision" value="approve"><input type="hidden" name="return" value="<?= e($here) ?>">
                            <button class="btn-black-lg" type="submit">Approve &amp; publish</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <p class="empty-state">No artworks in this tab.</p>
<?php endif; ?>
</section>

<?php require __DIR__ . '/../includes/admin_bottom.php'; ?>
