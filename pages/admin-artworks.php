<?php
/* Admin Space - every artwork in the gallery (opened from "All artworks" on the Overview). */
$pageTitle   = 'All artworks';
$adminActive = 'overview';
require __DIR__ . '/../includes/admin_top.php';

$pdo   = db();
$q     = trim((string) ($_GET['q'] ?? ''));
$page  = max(1, (int) ($_GET['page'] ?? 1));
$per   = 10;
$where = '';
$args  = [];
if ($q !== '') {
    $where = ' WHERE w.title LIKE ? OR a.name LIKE ?';
    $args  = ['%' . $q . '%', '%' . $q . '%'];
}
$countStmt = $pdo->prepare('SELECT COUNT(*) FROM artworks w JOIN artists a ON a.id = w.artist_id' . $where);
$countStmt->execute($args);
$total = (int) $countStmt->fetchColumn();
$pages = max(1, (int) ceil($total / $per));
$page  = min($page, $pages);

$stmt = $pdo->prepare(ADMIN_ARTWORK_SQL . $where . ' ORDER BY w.created_at DESC, w.id DESC LIMIT ' . $per . ' OFFSET ' . (($page - 1) * $per));
$stmt->execute($args);
$rows = $stmt->fetchAll();
$return = 'admin-artworks.php?' . http_build_query(['q' => $q, 'page' => $page]);
?>

<h1 class="admin-title">All artworks</h1>

<section class="admin-card">
    <div class="admin-card-head">
        <div><h2>Artwork library</h2><p>View and delete artworks in the gallery.</p></div>
        <a class="btn-outline-sm" href="admin-review.php">Review submissions &rarr;</a>
    </div>
    <form class="admin-search" method="get" action="admin-artworks.php">
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search by artwork title or artist" aria-label="Search artworks">
        <button class="btn-outline-sm" type="submit">Search</button>
    </form>
    <?php admin_artwork_table($rows, $return); ?>
    <?php require __DIR__ . '/../includes/admin_pager.php'; ?>
</section>

<?php require __DIR__ . '/../includes/admin_bottom.php'; ?>
