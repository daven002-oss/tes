<?php
/*
 * Search results (the header search box sends you here).   search.php?q=green
 * Looks through artwork titles/artists, artist names and showcase titles.
 * Not part of the header menu, so no menu item is underlined.
 */
require_once __DIR__ . '/../includes/init.php';

$q = trim((string) ($_GET['q'] ?? ''));

$foundArtworks = $foundArtists = $foundShowcases = [];
if ($q !== '') {
    $foundArtworks = array_filter($artworks, function ($a) use ($q) {
        return $a['section'] !== 'profile'
            && (stripos($a['title'], $q) !== false || stripos($a['artist'], $q) !== false);
    });
    $foundArtists = array_filter($artists, function ($a) use ($q) {
        return stripos($a['name'], $q) !== false;
    });
    $foundShowcases = array_filter($showcases, function ($s) use ($q) {
        return stripos($s['title'], $q) !== false || stripos($s['by'], $q) !== false;
    });
}
$total = count($foundArtworks) + count($foundArtists) + count($foundShowcases);

$pageTitle  = 'Search';
$activePage = '';
require __DIR__ . '/../includes/header.php';
?>

<div class="page-title">
    <h1>Search</h1>
    <p><?= $q === '' ? 'Type something in the search box above.' : $total . ' result(s) for "' . e($q) . '"' ?></p>
</div>

<?php if ($q !== '' && $total === 0): ?>
    <p class="empty-state">Nothing matched your search.</p>
<?php endif; ?>

<?php if ($foundArtworks): ?>
    <h2 class="result-head">Artworks</h2>
    <section class="grid-3">
        <?php foreach ($foundArtworks as $art): ?>
            <?php art_card($art); ?>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<?php if ($foundShowcases): ?>
    <h2 class="result-head">Artwork showcase</h2>
    <section class="grid-3">
        <?php foreach ($foundShowcases as $show): ?>
            <?php showcase_card($show); ?>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<?php if ($foundArtists): ?>
    <h2 class="result-head">Artists</h2>
    <ul class="result-list">
        <?php foreach ($foundArtists as $artist): ?>
            <li><a href="artist-profile.php?id=<?= (int) $artist['id'] ?>"><?= e($artist['name']) ?></a></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
