<?php
/*
 * Login state, backed by the database.
 *
 * A visitor is logged in when $_SESSION['user_id'] holds the id of a row in the `users` table.
 * login.php / signup.php call login_user(); the Log Out button calls logout_user().
 * Admins are the users whose `is_admin` column is 1.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

// ---------- Protection against forged form posts ----------
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_valid(?string $token): bool
{
    return is_string($token) && $token !== '' && hash_equals(csrf_token(), $token);
}

// A hidden field to put inside every <form method="post">
function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

// ---------- Who is logged in ----------
/*
 * The logged-in person (a row of `users` plus the things they saved), or null.
 * 'saved' = artwork ids they liked, 'saved_showcases' = showcase ids they liked,
 * 'following' = artist ids, 'scheduled' = event ids. Newest first.
 */
function current_user(): ?array
{
    static $user = false;
    if ($user !== false) {
        return $user;
    }

    $user = null;
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([(int) $_SESSION['user_id']]);
    $row = $stmt->fetch();
    if (!$row) {                       // the account was deleted: log out
        unset($_SESSION['user_id']);
        return null;
    }

    $ids = function (string $sql) use ($row): array {
        $s = db()->prepare($sql);
        $s->execute([$row['id']]);
        return array_map('intval', $s->fetchAll(PDO::FETCH_COLUMN));
    };

    $user = [
        'id'              => (int) $row['id'],
        'name'            => $row['display_name'],
        'username'        => $row['username'],
        'title'           => $row['title'],
        'bio'             => (string) $row['bio'],
        'location'        => (string) $row['location'],
        'joined'          => date('F Y', strtotime($row['created_at'])),
        'email'           => $row['email'],
        'avatar'          => (string) $row['avatar_path'],
        'is_admin'        => (bool) $row['is_admin'],
        'visibility'      => $row['profile_visibility'],
        'artist_id'       => $row['artist_id'] === null ? null : (int) $row['artist_id'],
        'saved'           => $ids('SELECT artwork_id  FROM artwork_likes   WHERE user_id = ? ORDER BY created_at DESC, artwork_id DESC'),
        'saved_showcases' => $ids('SELECT showcase_id FROM showcase_likes  WHERE user_id = ? ORDER BY created_at DESC, showcase_id DESC'),
        'following'       => $ids('SELECT artist_id   FROM artist_follows  WHERE user_id = ? ORDER BY created_at DESC, artist_id DESC'),
        'scheduled'       => $ids('SELECT event_id    FROM event_schedules WHERE user_id = ? ORDER BY created_at DESC, event_id DESC'),
    ];
    return $user;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function is_admin(): bool
{
    $user = current_user();
    return $user !== null && $user['is_admin'];
}

// Only the account in SUPER_ADMIN_EMAIL (includes/config.php) may make other users admins.
function is_super_admin(): bool
{
    $user = current_user();
    return $user !== null && $user['is_admin'] && strcasecmp($user['email'], SUPER_ADMIN_EMAIL) === 0;
}

// Call this right after a correct password (or a new sign up).
function login_user(int $userId, bool $remember = false): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
    if ($remember) {                   // keep the login for 30 days
        setcookie(session_name(), session_id(), [
            'expires'  => time() + 30 * 86400,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}
