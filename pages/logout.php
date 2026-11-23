<?php
// Log Out. The button on the profile page posts here; then you go back to the home page.
require_once __DIR__ . '/../includes/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_valid($_POST['csrf'] ?? null)) {
    logout_user();
}

header('Location: home.php');
exit;
