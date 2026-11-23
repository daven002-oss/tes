<?php
/*
 * Small JSON endpoint used by the buttons on the pages (heart, Follow, Schedule, Private/Public).
 * Every request is a POST with the CSRF token, and needs a logged-in user.
 *
 *   toggle_like     {type: 'artwork'|'showcase', id}   -> saves/removes it in "Saved artworks"
 *   toggle_follow   {id}                               -> follows/unfollows the artist
 *   toggle_schedule {id}                               -> schedules/unschedules the event
 *   set_visibility  {value: 'private'|'public'}        -> profile visibility
 */
require_once __DIR__ . '/../includes/init.php';

header('Content-Type: application/json');

function reply(int $status, array $data): void
{
    http_response_code($status);
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    reply(405, ['error' => 'POST only']);
}
if (!csrf_valid($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    reply(403, ['error' => 'bad token']);
}
$user = current_user();
if ($user === null) {
    reply(401, ['error' => 'login']);
}

$in     = json_decode(file_get_contents('php://input'), true) ?: [];
$action = (string) ($in['action'] ?? '');
$id     = (int) ($in['id'] ?? 0);

// What each toggle saves. Only these names are ever put into SQL.
$targets = [
    'toggle_like:artwork'   => ['artwork_likes',   'artwork_id'],
    'toggle_like:showcase'  => ['showcase_likes',  'showcase_id'],
    'toggle_follow'         => ['artist_follows',  'artist_id'],
    'toggle_schedule'       => ['event_schedules', 'event_id'],
];
$key = $action === 'toggle_like' ? 'toggle_like:' . (string) ($in['type'] ?? '') : $action;

if (isset($targets[$key])) {
    [$table, $column] = $targets[$key];
    if ($id < 1) {
        reply(400, ['error' => 'bad id']);
    }
    $pdo = db();
    $del = $pdo->prepare("DELETE FROM $table WHERE user_id = ? AND $column = ?");
    $del->execute([$user['id'], $id]);
    $on = false;
    if ($del->rowCount() === 0) {                 // it was not saved yet: save it
        try {
            $pdo->prepare("INSERT INTO $table (user_id, $column) VALUES (?, ?)")->execute([$user['id'], $id]);
            $on = true;
        } catch (PDOException $e) {
            reply(404, ['error' => 'not found']); // that artwork/artist/event does not exist
        }
    }
    $out = ['ok' => true, 'on' => $on];
    if ($action === 'toggle_follow') {
        $count = $pdo->prepare('SELECT COUNT(*) FROM artist_follows WHERE artist_id = ?');
        $count->execute([$id]);
        $out['followers'] = (int) $count->fetchColumn();
    }
    reply(200, $out);
}

if ($action === 'set_visibility') {
    $value = (string) ($in['value'] ?? '');
    if (!in_array($value, ['private', 'public'], true)) {
        reply(400, ['error' => 'bad value']);
    }
    db()->prepare('UPDATE users SET profile_visibility = ? WHERE id = ?')->execute([$value, $user['id']]);
    reply(200, ['ok' => true]);
}

reply(400, ['error' => 'unknown action']);
