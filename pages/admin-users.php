<?php
/*
 * Admin Space - Users: everyone in the `users` table.
 * Columns: Username | Joined at | Status (admin / user) | Action (Delete).
 * Only SUPER_ADMIN_EMAIL can make someone an admin (the button under Status).
 */
$pageTitle   = 'Users';
$adminActive = 'users';
require __DIR__ . '/../includes/admin_top.php';

$pdo = db();
$count = function (string $sql) use ($pdo): int { return (int) $pdo->query($sql)->fetchColumn(); };
$totalUsers = $count('SELECT COUNT(*) FROM users');
$admins     = $count('SELECT COUNT(*) FROM users WHERE is_admin = 1');
$newUsers   = $count("SELECT COUNT(*) FROM users WHERE created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')");

$q     = trim((string) ($_GET['q'] ?? ''));
$page  = max(1, (int) ($_GET['page'] ?? 1));
$per   = 10;
$where = $q !== '' ? ' WHERE username LIKE ? OR email LIKE ?' : '';
$args  = $q !== '' ? ['%' . $q . '%', '%' . $q . '%'] : [];

$countStmt = $pdo->prepare('SELECT COUNT(*) FROM users' . $where);
$countStmt->execute($args);
$total = (int) $countStmt->fetchColumn();
$pages = max(1, (int) ceil($total / $per));
$page  = min($page, $pages);

$stmt = $pdo->prepare('SELECT id, username, email, is_admin, avatar_path, display_name, created_at FROM users' . $where
    . ' ORDER BY is_admin DESC, created_at DESC, id DESC LIMIT ' . $per . ' OFFSET ' . (($page - 1) * $per));
$stmt->execute($args);
$rows   = $stmt->fetchAll();
$return = 'admin-users.php?' . http_build_query(['q' => $q, 'page' => $page]);
?>

<h1 class="admin-title">Users</h1>

<section class="stat-row stat-3">
    <div class="stat"><span>Total users</span><strong><?= $totalUsers ?></strong><small>Everyone with an account</small></div>
    <div class="stat"><span>Admins</span><strong><?= $admins ?></strong><small>Can open the Admin Space</small></div>
    <div class="stat"><span>New this month</span><strong><?= $newUsers ?></strong><small>Joined since the 1st</small></div>
</section>

<section class="admin-card">
    <div class="admin-card-head">
        <div><h2>All users</h2><p>Everyone registered on Artspace.<?= is_super_admin() ? '' : ' Only ' . e(SUPER_ADMIN_EMAIL) . ' can make someone an admin.' ?></p></div>
    </div>
    <form class="admin-search" method="get" action="admin-users.php">
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search by username or email" aria-label="Search users">
        <button class="btn-outline-sm" type="submit">Search</button>
    </form>

    <div class="table-wrap">
        <table class="admin-table">
            <thead><tr><th>Username</th><th>Joined at</th><th>Status</th><th class="col-action">Action</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $r):
                $isMain = strcasecmp($r['email'], SUPER_ADMIN_EMAIL) === 0;
                $isMe   = (int) $r['id'] === $adminUser['id'];
                $canDelete = !$isMain && !$isMe && (!$r['is_admin'] || is_super_admin());
                $canPromote = is_super_admin() && !$isMain && !$isMe;
            ?>
                <tr>
                    <td>
                        <div class="cell-art">
                            <span class="thumb round"><?= avatar_html(['avatar' => $r['avatar_path'], 'name' => $r['display_name']]) ?></span>
                            <span><strong><?= e($r['username']) ?></strong><small class="sub-line"><?= e($r['email']) ?></small></span>
                        </div>
                    </td>
                    <td><?= e(date('d M Y', strtotime($r['created_at']))) ?></td>
                    <td>
                        <span class="pill <?= $r['is_admin'] ? 'pill-admin' : 'pill-user' ?>"><?= $r['is_admin'] ? 'Admin' : 'User' ?></span>
                        <?php if ($canPromote): ?>
                            <form class="inline-form" method="post" action="admin-action.php" data-confirm="<?= $r['is_admin'] ? 'Remove admin rights from ' : 'Make ' ?><?= e($r['username']) ?><?= $r['is_admin'] ? '?' : ' an admin? They will be able to open the Admin Space.' ?>" data-confirm-yes="<?= $r['is_admin'] ? 'Yes, remove' : 'Yes, make admin' ?>" data-confirm-title="Change role?" data-confirm-tone="neutral">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="set_admin">
                                <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                <input type="hidden" name="make_admin" value="<?= $r['is_admin'] ? '0' : '1' ?>">
                                <input type="hidden" name="return" value="<?= e($return) ?>">
                                <button class="link-small" type="submit"><?= $r['is_admin'] ? 'Make user' : 'Make admin' ?></button>
                            </form>
                        <?php endif; ?>
                    </td>
                    <td class="col-action">
                        <?php if ($canDelete): ?>
                            <form method="post" action="admin-action.php" data-confirm="Delete the account &ldquo;<?= e($r['username']) ?>&rdquo;? Their saved artworks, follows, scheduled events and comments are removed too. This can't be undone.">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete_user">
                                <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                <input type="hidden" name="return" value="<?= e($return) ?>">
                                <button class="link-delete" type="submit">Delete</button>
                            </form>
                        <?php else: ?>
                            <span class="muted-sm"><?= $isMe ? 'You' : '&mdash;' ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?><tr><td colspan="4" class="table-empty">No users found.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php require __DIR__ . '/../includes/admin_pager.php'; ?>
</section>

<?php require __DIR__ . '/../includes/admin_bottom.php'; ?>
