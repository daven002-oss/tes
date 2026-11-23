<?php
/*
 * Shared header - used by every page.
 *
 * Each page sets these before including this file:
 *   $pageTitle  - text for the browser tab
 *   $activePage - which menu item gets the underline:
 *                 'home', 'new', 'artists', 'showcase', 'events'
 *                 Leave it '' (or unset) for pages that are not in the menu
 *                 (login, sign up, about, contact, artist profile, ...).
 */
require_once __DIR__ . '/init.php';

$activePage = $activePage ?? '';
$pageTitle  = $pageTitle ?? 'Home';

$nav = [
    'home'     => ['Home',            'home.php'],
    'new'      => ['New',             'new.php'],
    'artists'  => ['Artists',         'artists.php'],
    'showcase' => ['Artwork showcase', 'showcase.php'],
    'events'   => ['Artspace Events', 'events.php'],
];

$logoFile     = dirname(__DIR__) . '/' . $logoImage;
$onLoginPage  = basename($_SERVER['SCRIPT_NAME']) === 'login.php';
$searchQuery  = isset($_GET['q']) && basename($_SERVER['SCRIPT_NAME']) === 'search.php' ? (string) $_GET['q'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($pageTitle) ?> | <?= e($siteName) ?> Art Space</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poiret+One&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<header class="site-header<?= $searchQuery !== '' ? ' search-open' : '' ?>">
    <div class="header-inner">
        <a class="logo" href="home.php" aria-label="<?= e($siteName) ?> - go to home">
            <?php if (is_file($logoFile)): ?>
                <img src="../<?= e($logoImage) ?>" alt="<?= e($siteName) ?> logo">
            <?php else: ?>
                <span class="logo-placeholder">LOGO</span>
            <?php endif; ?>
        </a>

        <nav class="site-nav" aria-label="Main">
            <?php foreach ($nav as $key => [$label, $href]): ?>
                <a href="<?= e($href) ?>"<?= $activePage === $key ? ' class="active" aria-current="page"' : '' ?>><?= e($label) ?></a>
            <?php endforeach; ?>
        </nav>

        <div class="header-actions">
            <!-- Click the magnifier: the search box slides open. Press Enter to search. Esc closes it. -->
            <form class="search<?= $searchQuery !== '' ? ' open' : '' ?>" role="search" action="search.php" method="get">
                <input id="search-input" type="search" name="q" value="<?= e($searchQuery) ?>" placeholder="Search" aria-label="Search artworks, artists and showcases" autocomplete="off">
                <button class="search-btn" type="button" aria-label="Search" aria-controls="search-input" aria-expanded="<?= $searchQuery !== '' ? 'true' : 'false' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="10.5" cy="10.5" r="7"/><line x1="15.8" y1="15.8" x2="21" y2="21"/></svg>
                </button>
            </form>
            <?php if (is_logged_in()): ?>
                <!-- Logged in: the Login button becomes a round button with your profile picture. -->
                <a class="btn-profile" href="user-profile.php" aria-label="Your profile" title="Your profile"><?= avatar_html(current_user()) ?></a>
            <?php else: ?>
                <a class="btn-outline<?= $onLoginPage ? ' is-current' : '' ?>" href="login.php">Login</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<main>
