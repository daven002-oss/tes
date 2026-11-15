<?php
/*
 * VELA - small helper functions used by every page.
 */

// Safely print text inside HTML.
function e($text): string
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

// Find one item (artwork, artist, event, showcase) in an array by its 'id'.
function find_by_id(array $items, $id): ?array
{
    foreach ($items as $item) {
        if ((int) $item['id'] === (int) $id) {
            return $item;
        }
    }
    return null;
}

// Show a picture if the file exists, otherwise a grey placeholder box.
// $src is relative to the project root, e.g. 'images/artworks/art1.jpg'
function render_image(string $src, string $alt = 'Image'): string
{
    $file = dirname(__DIR__) . '/' . ltrim($src, '/');
    if ($src !== '' && is_file($file)) {
        return '<img src="../' . e(ltrim($src, '/')) . '" alt="' . e($alt) . '">';
    }
    return '<div class="placeholder" role="img" aria-label="' . e($alt) . '"><span>' . e($alt) . '</span></div>';
}

// Card used on the New page, search results and artist profiles.
function art_card(array $art, bool $liked = false): void
{ ?>
    <article class="art-card">
        <a class="art-link" href="artwork-detail.php?id=<?= (int) $art['id'] ?>">
            <div class="media r-53"><?= render_image($art['image'], $art['title']) ?></div>
            <div class="art-meta">
                <strong><?= e($art['title']) ?></strong>
                <span>By : <?= e($art['artist']) ?></span>
            </div>
        </a>
        <button class="heart<?= $liked ? ' liked' : '' ?>" type="button" aria-pressed="<?= $liked ? 'true' : 'false' ?>" aria-label="Like <?= e($art['title']) ?>"><?= $liked ? '&#9829;' : '&#9825;' ?></button>
    </article>
<?php }

// Card used on the Artwork showcase page.
function showcase_card(array $show): void
{ ?>
    <article class="art-card">
        <a class="art-link" href="showcase-detail.php?id=<?= (int) $show['id'] ?>">
            <div class="media r-53"><?= render_image($show['image'], $show['title']) ?></div>
            <div class="art-meta">
                <strong><?= e($show['title']) ?></strong>
                <span>By : <?= e($show['by']) ?></span>
            </div>
        </a>
        <button class="heart" type="button" aria-pressed="false" aria-label="Like <?= e($show['title']) ?>">&#9825;</button>
    </article>
<?php }

// Small card used in the home page scroller.
function mini_card(array $art, bool $hidden = false): void
{ ?>
    <a class="mini-card" href="artwork-detail.php?id=<?= (int) $art['id'] ?>"<?= $hidden ? ' aria-hidden="true" tabindex="-1"' : '' ?>>
        <div class="media r-34"><?= render_image($art['image'], $art['title']) ?></div>
        <strong><?= e($art['title']) ?></strong>
        <span>By : <?= e($art['artist']) ?></span>
    </a>
<?php }

// A person's profile picture for small round buttons: their photo, or their initials if they have none.
function avatar_html(array $user): string
{
    $src  = (string) ($user['avatar'] ?? '');
    $file = dirname(__DIR__) . '/' . ltrim($src, '/');
    if ($src !== '' && is_file($file)) {
        return '<img src="../' . e(ltrim($src, '/')) . '" alt="">';
    }
    $initials = '';
    foreach (array_slice(preg_split('/\s+/', trim($user['name'])), 0, 2) as $word) {
        $initials .= strtoupper(substr($word, 0, 1));
    }
    return '<span class="avatar-initials" aria-hidden="true">' . e($initials) . '</span>';
}
