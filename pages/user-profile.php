<?php
/*
 * "My profile" page - opened from the round profile-picture button in the header.
 * Only for logged-in visitors (others are sent to the login page).
 *
 * Normal users see Log Out. Admins also see an "Admin Overview" button.
 * The person's details come from current_user() in includes/auth.php.
 */
require_once __DIR__ . '/../includes/init.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$user = current_user();

// Everything below is what this person saved, newest first (see the *_likes / artist_follows / event_schedules tables).
$savedArtworks   = pick_by_ids($artworks, $user['saved']);
$savedShowcases  = pick_by_ids($showcases, $user['saved_showcases']);
$savedCount      = count($savedArtworks) + count($savedShowcases);
$followedArtists = pick_by_ids($artists, $user['following']);
$scheduledEvents = pick_by_ids($events, $user['scheduled']);

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
            <li><strong><?= $savedCount ?></strong><span>Saved artworks</span></li>
            <li><strong><?= count($followedArtists) ?></strong><span>Following</span></li>
            <li><strong><?= count($scheduledEvents) ?></strong><span>Scheduled events</span></li>
        </ul>
    </div>
</section>

<div class="up-layout">
    <div class="up-main">
        <div class="tabs" role="tablist">
            <button class="tab active" type="button" role="tab" aria-selected="true" data-tab="saved">Saved artworks <small><?= $savedCount ?></small></button>
            <button class="tab" type="button" role="tab" aria-selected="false" data-tab="following">Following</button>
            <button class="tab" type="button" role="tab" aria-selected="false" data-tab="scheduled">Scheduled events <small><?= count($scheduledEvents) ?></small></button>
        </div>

        <div class="tab-panel" data-panel="saved">
            <div class="panel-head">
                <p>A little inspiration, saved for later.</p>
                <span>Recently saved</span>
            </div>
            <?php if ($savedCount): ?>
                <div class="grid-3 up-grid">
                    <?php foreach ($savedArtworks as $art): ?>
                        <?php art_card($art, true); ?>
                    <?php endforeach; ?>
                    <?php foreach ($savedShowcases as $show): ?>
                        <?php showcase_card($show, true); ?>
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
            <button type="button" data-visibility="private" class="<?= $user['visibility'] === 'private' ? 'on' : '' ?>" aria-pressed="<?= $user['visibility'] === 'private' ? 'true' : 'false' ?>">Private</button>
            <button type="button" data-visibility="public" class="<?= $user['visibility'] === 'public' ? 'on' : '' ?>" aria-pressed="<?= $user['visibility'] === 'public' ? 'true' : 'false' ?>">Public</button>
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
                <?= csrf_field() ?>
                <button class="btn-logout" type="submit">Log Out</button>
            </form>
            <?php if (is_admin()): ?>
                <a class="btn-admin" href="admin.php">Admin Overview</a>
            <?php endif; ?>
        </div>
    </aside>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
