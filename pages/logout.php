<?php
// Log Out. The button on the profile page posts here; then you go back to the home page.
require_once __DIR__ . '/../includes/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION = [];
    session_destroy();
}

header('Location: home.php');
exit;
