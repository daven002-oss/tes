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
function art_card(array $art, ?bool $liked = null): void
{
    $liked = $liked ?? is_saved('artwork', (int) $art['id']); ?>
    <article class="art-card">
        <a class="art-link" href="artwork-detail.php?id=<?= (int) $art['id'] ?>">
            <div class="media r-53"><?= render_image($art['image'], $art['title']) ?></div>
            <div class="art-meta">
                <strong><?= e($art['title']) ?></strong>
                <span>By : <?= e($art['artist']) ?></span>
            </div>
        </a>
        <button class="heart<?= $liked ? ' liked' : '' ?>" type="button" data-like="artwork" data-id="<?= (int) $art['id'] ?>" aria-pressed="<?= $liked ? 'true' : 'false' ?>" aria-label="Save <?= e($art['title']) ?>"><?= $liked ? '&#9829;' : '&#9825;' ?></button>
    </article>
<?php }

// Card used on the Artwork showcase page.
function showcase_card(array $show, ?bool $liked = null): void
{
    $liked = $liked ?? is_saved('showcase', (int) $show['id']); ?>
    <article class="art-card">
        <a class="art-link" href="showcase-detail.php?id=<?= (int) $show['id'] ?>">
            <div class="media r-53"><?= render_image($show['image'], $show['title']) ?></div>
            <div class="art-meta">
                <strong><?= e($show['title']) ?></strong>
                <span>By : <?= e($show['by']) ?></span>
            </div>
        </a>
        <button class="heart<?= $liked ? ' liked' : '' ?>" type="button" data-like="showcase" data-id="<?= (int) $show['id'] ?>" aria-pressed="<?= $liked ? 'true' : 'false' ?>" aria-label="Save <?= e($show['title']) ?>"><?= $liked ? '&#9829;' : '&#9825;' ?></button>
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

// ---------- What the logged-in person saved ----------
// $type: 'artwork' (liked), 'showcase' (liked), 'artist' (followed), 'event' (scheduled)
function is_saved(string $type, int $id): bool
{
    $user = current_user();
    if ($user === null) {
        return false;
    }
    $key = ['artwork' => 'saved', 'showcase' => 'saved_showcases', 'artist' => 'following', 'event' => 'scheduled'][$type] ?? null;
    return $key !== null && in_array($id, $user[$key], true);
}

// Pick items out of a list (artworks, artists...) in the order of $ids.
function pick_by_ids(array $items, array $ids): array
{
    $out = [];
    foreach ($ids as $id) {
        $item = find_by_id($items, $id);
        if ($item !== null) {
            $out[] = $item;
        }
    }
    return $out;
}

// ---------- Comments (under an artwork or a showcase) ----------
// $type: 'artwork' or 'showcase'
function get_comments(string $type, int $id): array
{
    $column = $type === 'showcase' ? 'showcase_id' : 'artwork_id';
    $stmt = db()->prepare(
        "SELECT u.display_name AS name, c.created_at, c.body
           FROM comments c JOIN users u ON u.id = c.user_id
          WHERE c.$column = ?
          ORDER BY c.created_at, c.id"
    );
    $stmt->execute([$id]);
    $out = [];
    foreach ($stmt->fetchAll() as $r) {
        $out[] = ['name' => $r['name'], 'date' => date('M j, Y', strtotime($r['created_at'])), 'text' => $r['body']];
    }
    return $out;
}

// Call this near the top of a page that shows comments. If a comment was just posted,
// it saves it and reloads the page (so refreshing does not post it twice).
function handle_comment_post(string $type, int $id): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['comment'])) {
        return;
    }
    $user = current_user();
    $body = trim((string) $_POST['comment']);
    if ($user !== null && csrf_valid($_POST['csrf'] ?? null) && $body !== '' && mb_strlen($body) <= 1000) {
        $column = $type === 'showcase' ? 'showcase_id' : 'artwork_id';
        $stmt = db()->prepare("INSERT INTO comments (user_id, $column, body) VALUES (?, ?, ?)");
        try {
            $stmt->execute([$user['id'], $id, $body]);
        } catch (PDOException $e) {
            // the artwork/showcase does not exist: ignore
        }
    }
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '#') . '#comments-title');
    exit;
}

// ---------- One-time messages shown after a form (saved, error...) ----------
function flash_set(string $message, string $kind = 'ok'): void
{
    $_SESSION['flash'] = ['message' => $message, 'kind' => $kind];
}

// Returns the message once, then forgets it.
function flash_html(): string
{
    if (empty($_SESSION['flash'])) {
        return '';
    }
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return '<p class="notice ' . ($f['kind'] === 'error' ? 'error' : 'success') . '" role="status">' . e($f['message']) . '</p>';
}

// "2h ago", "3d ago"...
function time_ago(string $datetime): string
{
    $diff = max(0, time() - strtotime($datetime));
    if ($diff < 3600)  { return max(1, (int) ($diff / 60)) . 'm ago'; }
    if ($diff < 86400) { return (int) ($diff / 3600) . 'h ago'; }
    return (int) ($diff / 86400) . 'd ago';
}

// Artwork status (artworks.status) as words, and its CSS class.
function status_label(string $status): string
{
    return ['published' => 'Published', 'in_review' => 'In review', 'draft' => 'Draft'][$status] ?? $status;
}

/*
 * Deletes an artwork (and its likes, comments, picture row and uploaded file).
 * Returns false if it does not exist.
 */
function delete_artwork(int $id): bool
{
    $pdo = db();
    $pic = $pdo->prepare("SELECT path FROM pictures WHERE owner_type = 'artwork' AND owner_id = ?");
    $pic->execute([$id]);
    $path = $pic->fetchColumn();

    $del = $pdo->prepare('DELETE FROM artworks WHERE id = ?');
    $del->execute([$id]);
    if ($del->rowCount() === 0) {
        return false;
    }
    delete_uploaded_file($path ?: null);     // the trigger already removed the `pictures` row
    return true;
}

// Sends the browser to a page and stops.
function redirect(string $to): void
{
    header('Location: ' . $to);
    exit;
}

