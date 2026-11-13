<?php
/*
 * Sign up page (opened from the "Sign up" link on the login page).
 * Not part of the header menu, so no menu item is underlined.
 * There is no database yet, so submitting the form only shows a notice.
 */
require_once __DIR__ . '/../includes/init.php';

$notice = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $notice = 'Sign up is not connected to a database yet.';
}

$pageTitle  = 'Sign Up';
$activePage = '';
require __DIR__ . '/../includes/header.php';
?>

<section class="auth">
    <h1>Artspace<br>Create Account</h1>
    <p class="sub">sign up for your account</p>

    <?php if ($notice): ?>
        <p class="notice"><?= e($notice) ?></p>
    <?php endif; ?>

    <form method="post" action="signup.php">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required>

        <label for="email">Email address</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <button class="btn-auth" type="submit">Sign up</button>
        <p class="auth-switch">Already have an account? <a href="login.php">Login</a></p>
    </form>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
