<?php
/*
 * Login page. Not part of the header menu, so no menu item is underlined.
 * Checks the email + password against the `users` table.
 */
require_once __DIR__ . '/../includes/init.php';

if (is_logged_in()) {
    header('Location: user-profile.php');
    exit;
}

$error = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $stmt = db()->prepare('SELECT id, password_hash FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $row = $stmt->fetch();

    if (!csrf_valid($_POST['csrf'] ?? null)) {
        $error = 'Your session expired. Please try again.';
    } elseif ($row && password_verify($password, $row['password_hash'])) {
        login_user((int) $row['id'], !empty($_POST['remember']));
        header('Location: home.php');
        exit;
    } else {
        $error = 'Wrong email address or password.';
    }
}

$pageTitle  = 'Login';
$activePage = '';
require __DIR__ . '/../includes/header.php';
?>

<section class="auth">
    <h1>Artspace<br>Welcome Back</h1>
    <p class="sub">sign in to your account</p>

    <?php if ($error): ?>
        <p class="notice error" role="alert"><?= e($error) ?></p>
    <?php endif; ?>

    <form method="post" action="login.php">
        <?= csrf_field() ?>
        <label for="email">Email address</label>
        <input type="email" id="email" name="email" value="<?= e($email) ?>" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <div class="auth-extras">
            <label class="check"><input type="checkbox" name="remember"> Remember me</label>
            <a href="#">Forgot password?</a>
        </div>

        <button class="btn-auth" type="submit">Login</button>
        <p class="auth-switch">Don't have an account? <a href="signup.php">Sign up</a></p>
    </form>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
