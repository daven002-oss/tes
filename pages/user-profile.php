<?php
/*
 * "My profile" page - opened from the round profile-picture button in the header.
 * Only for logged-in visitors (nobody can be logged in until the database exists,
 * so for now this page sends everyone to the login page).
 *
 * Normal users see Log Out. Admins also see an "Admin Overview" button.
 * The person's details come from current_user() in includes/auth.php.
 */
require_once __DIR__ . '/../includes/init.php';

// if (!is_logged_in()) {
//     header('Location: login.php');
//     exit;
// }

$user = current_user();

$savedArtworks = array_filter($artworks, function ($a) use ($user) {
    return in_array($a['id'], $user['saved'], true);
});
$followedArtists = array_filter($artists, function ($a) use ($user) {
    return in_array($a['id'], $user['following'], true);
});
$scheduledEvents = array_filter($events, function ($e) use ($user) {
    return in_array($e['id'], $user['scheduled'], true);
});

$pageTitle  = 'My profile';
$activePage = '';
require __DIR__ . '/../includes/header.php';
?>

<div class="page-title">
    <h1>My profile</h1>
    <p>Your own little corner of Artspace.</p>
</div>

<section class="up-card">
    <div class="media r-34 up-photo"><?= render_image($user['avatar'], $user['name']) ?></div>

    <div class="up-info">
        <h2><?= e($user['name']) ?></h2>
        <p class="up-handle">@<?= e($user['username']) ?> &middot; <?= e($user['title']) ?></p>
        <p class="up-bio"><?= e($user['bio']) ?></p>
        <p class="up-meta"><?= e($user['location']) ?> &middot; Joined <?= e($user['joined']) ?></p>
    </div>

    <div class="up-side-top">
        <a class="btn-pill" href="#" title="Editing needs the database">Edit profile</a>
        <ul class="up-stats">
            <li><strong><?= count($savedArtworks) ?></strong><span>Saved artworks</span></li>
            <li><strong><?= count($followedArtists) ?></strong><span>Following</span></li>
            <li><strong><?= count($scheduledEvents) ?></strong><span>Scheduled events</span></li>
        </ul>
    </div>
</section>

<div class="up-layout">
    <div class="up-main">
        <div class="tabs" role="tablist">
            <button class="tab active" type="button" role="tab" aria-selected="true" data-tab="saved">Saved artworks <small><?= count($savedArtworks) ?></small></button>
            <button class="tab" type="button" role="tab" aria-selected="false" data-tab="following">Following</button>
            <button class="tab" type="button" role="tab" aria-selected="false" data-tab="scheduled">Scheduled events <small><?= count($scheduledEvents) ?></small></button>
        </div>

        <div class="tab-panel" data-panel="saved">
            <div class="panel-head">
                <p>A little inspiration, saved for later.</p>
                <span>Recently saved</span>
            </div>
            <?php if ($savedArtworks): ?>
                <div class="grid-3 up-grid">
                    <?php foreach ($savedArtworks as $art): ?>
                        <?php art_card($art, true); ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="empty-state">You haven't saved any artworks yet.</p>
            <?php endif; ?>
        </div>

        <div class="tab-panel" data-panel="following" hidden>
            <div class="panel-head"><p>Artists you follow.</p></div>
            <ul class="result-list">
                <?php foreach ($followedArtists as $artist): ?>
                    <li><a href="artist-profile.php?id=<?= (int) $artist['id'] ?>"><?= e($artist['name']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="tab-panel" data-panel="scheduled" hidden>
            <div class="panel-head"><p>Events you've scheduled.</p></div>
            <?php if ($scheduledEvents): ?>
                <?php foreach ($scheduledEvents as $event): ?>
                    <article class="event-row">
                        <div class="media r-169"><?= render_image($event['image'], $event['title']) ?></div>
                        <div>
                            <p class="location"><?= e($event['location']) ?></p>
                            <h2><?= e($event['title']) ?></h2>
                            <p class="desc"><?= e($event['description']) ?></p>
                        </div>
                        <a class="btn" href="event-schedule.php?id=<?= (int) $event['id'] ?>">Schedule</a>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="empty-state">You haven't scheduled any events yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <aside class="up-side">
        <h3>Account details</h3>
        <div class="toggle" role="group" aria-label="Profile visibility">
            <button class="on" type="button" aria-pressed="true">Private</button>
            <button type="button" aria-pressed="false">Public</button>
        </div>

        <dl class="up-details">
            <dt>Email address</dt>
            <dd><?= e($user['email']) ?></dd>
            <dt>Member since</dt>
            <dd><?= e($user['joined']) ?></dd>
        </dl>

        <h3 class="settings-title">Account settings</h3>
        <div class="up-actions<?= is_admin() ? '' : ' solo' ?>">
            <form method="post" action="logout.php">
                <button class="btn-logout" type="submit">Log Out</button>
            </form>
            <?php if (is_admin()): ?>
                <a class="btn-admin" href="admin.php">Admin Overview</a>
            <?php endif; ?>
        </div>
    </aside>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
