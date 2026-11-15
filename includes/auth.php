<?php
/*
 * Login state.
 *
 * There is no database yet, so nothing ever logs a visitor in and
 * is_logged_in() is always false. That keeps every "logged in" page out of reach for now:
 *   - the round profile-picture button in the header (instead of Login)
 *   - the user profile page and Admin Overview page
 *   - the comment box on artwork and showcase pages
 *
 * When you add the database, make login.php do this after a correct password:
 *     $_SESSION['user_id']  = $userRow['id'];
 *     $_SESSION['is_admin'] = (bool) $userRow['is_admin'];   // true only for admins
 * and the logged-in pages switch on by themselves.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

function is_admin(): bool
{
    return is_logged_in() && !empty($_SESSION['is_admin']);
}

/*
 * The person who is logged in, or null if nobody is.
 * PLACEHOLDER DATA: when the database exists, replace the array below with a lookup of
 * the user whose id is $_SESSION['user_id'].
 * 'avatar' => ''  shows the person's initials; put a picture path like 'images/users/alya.jpg' to show a photo.
 * 'saved' = ids of saved artworks, 'following' = ids of followed artists (see includes/data.php).
 */
function current_user(): ?array
{
    if (!is_logged_in()) {
        return null;
    }
    return [
        'id'          => $_SESSION['user_id'],
        'name'        => 'Alya Putri',
        'username'    => 'alyaputri',
        'title'       => 'Art enthusiast',
        'bio'         => 'Finding inspiration in everyday colours, quiet landscapes, and the artists who see things a little differently.',
        'location'    => 'Jakarta, Indonesia',
        'joined'      => 'November 2026',
        'email'       => 'alya.putri@gmail.com',
        'avatar'      => '',
        'is_admin'    => is_admin(),
        'saved'       => [7, 8, 9],
        'following'   => [1, 2, 3, 4],
        'collections' => ['Landscapes', 'Favourites'],
    ];
}
