<?php
/*
 * Login state.
 *
 * There is no database yet, so nothing ever logs a visitor in and
 * is_logged_in() is always false. That keeps the "logged in" pages
 * (the Profile button in the header, the comment box on a showcase page)
 * out of reach for now.
 *
 * When you add the database, make login.php do this after a correct password:
 *     $_SESSION['user_id'] = $userRow['id'];
 * and the logged-in pages switch on by themselves.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}
