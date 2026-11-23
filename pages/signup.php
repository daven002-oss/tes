<?php
/*
 * Sign up page (opened from the "Sign up" link on the login page).
 * Creates a row in the `users` table (the password is stored as a hash, never as text),
 * logs the new person in, and sends them to the home page.
 */
require_once __DIR__ . '/../includes/init.php';

if (is_logged_in()) {
    header('Location: user-profile.php');
    exit;
}

$error    = '';
$username = '';
$email    = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $email    = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!csrf_valid($_POST['csrf'] ?? null)) {
        $error = 'Your session expired. Please try again.';
    } elseif (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) {
        $error = 'Username must be 3-30 characters: letters, numbers or underscore.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 120) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } else {
        $pdo = db();
        $taken = $pdo->prepare('SELECT username = ?, email = ? FROM users WHERE username = ? OR email = ? LIMIT 1');
        $taken->execute([$username, $email, $username, $email]);
        $dup = $taken->fetch(PDO::FETCH_NUM);
        if ($dup && $dup[0]) {
            $error = 'That username is already taken.';
        } elseif ($dup) {
            $error = 'An account with that email address already exists.';
        } else {
            try {
                $pdo->prepare('INSERT INTO users (username, email, password_hash, display_name) VALUES (?, ?, ?, ?)')
                    ->execute([$username, $email, password_hash($password, PASSWORD_DEFAULT), $username]);
                login_user((int) $pdo->lastInsertId());
                header('Location: home.php');
                exit;
            } catch (PDOException $e) {
                $error = 'Could not create the account. Please try again.';
            }
        }
    }
}

$pageTitle  = 'Sign Up';
$activePage = '';
require __DIR__ . '/../includes/header.php';
?>

<section class="auth">
    <h1>Artspace<br>Create Account</h1>
    <p class="sub">sign up for your account</p>

    <?php if ($error): ?>
        <p class="notice error" role="alert"><?= e($error) ?></p>
    <?php endif; ?>

    <form method="post" action="signup.php">
        <?= csrf_field() ?>
        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?= e($username) ?>" maxlength="30" required>

        <label for="email">Email address</label>
        <input type="email" id="email" name="email" value="<?= e($email) ?>" maxlength="120" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" minlength="8" required>

        <button class="btn-auth" type="submit">Sign up</button>
        <p class="auth-switch">Already have an account? <a href="login.php">Login</a></p>
    </form>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
