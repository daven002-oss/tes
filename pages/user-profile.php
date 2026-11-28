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

// Artworks this person posted (any status), newest first.
$myStmt = db()->prepare(
    "SELECT w.id, w.title, w.status, pic.path AS image
       FROM artworks w
       LEFT JOIN pictures pic ON pic.owner_type = 'artwork' AND pic.owner_id = w.id
      WHERE w.submitted_by = ?
      ORDER BY w.created_at DESC, w.id DESC"
);
$myStmt->execute([$user['id']]);
$myArtworks = $myStmt->fetchAll();

$validTabs = ['saved', 'following', 'scheduled', 'artworks'];
$activeTab = in_array($_GET['tab'] ?? '', $validTabs, true) ? $_GET['tab'] : 'saved';

$pageTitle  = 'My profile';
$activePage = '';
require __DIR__ . '/../includes/header.php';
?>

<div class="page-title">
    <h1>My profile</h1>
    <p>Your own little corner of Artspace.</p>
</div>

<?= flash_html() ?>

<section class="up-card">
    <div class="media r-34 up-photo"><?= render_image($user['avatar'], $user['name']) ?></div>

    <div class="up-info">
        <h2><?= e($user['name']) ?></h2>
        <p class="up-handle">@<?= e($user['username']) ?> &middot; <?= e($user['title']) ?></p>
        <p class="up-bio"><?= e($user['bio']) ?></p>
        <p class="up-meta"><?= e($user['location']) ?> &middot; Joined <?= e($user['joined']) ?></p>
    </div>

    <div class="up-side-top">
        <button class="btn-pill" type="button" data-open-dialog="edit-profile">Edit profile</button>
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
            <button class="tab<?= $activeTab === 'saved' ? ' active' : '' ?>" type="button" role="tab" aria-selected="<?= $activeTab === 'saved' ? 'true' : 'false' ?>" data-tab="saved">Saved artworks <small><?= $savedCount ?></small></button>
            <button class="tab<?= $activeTab === 'following' ? ' active' : '' ?>" type="button" role="tab" aria-selected="<?= $activeTab === 'following' ? 'true' : 'false' ?>" data-tab="following">Following</button>
            <button class="tab<?= $activeTab === 'scheduled' ? ' active' : '' ?>" type="button" role="tab" aria-selected="<?= $activeTab === 'scheduled' ? 'true' : 'false' ?>" data-tab="scheduled">Scheduled events <small><?= count($scheduledEvents) ?></small></button>
            <button class="tab<?= $activeTab === 'artworks' ? ' active' : '' ?>" type="button" role="tab" aria-selected="<?= $activeTab === 'artworks' ? 'true' : 'false' ?>" data-tab="artworks">My artworks <small><?= count($myArtworks) ?></small></button>
        </div>

        <div class="tab-panel" data-panel="saved"<?= $activeTab === 'saved' ? '' : ' hidden' ?>>
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

        <div class="tab-panel" data-panel="following"<?= $activeTab === 'following' ? '' : ' hidden' ?>>
            <div class="panel-head"><p>Artists you follow.</p></div>
            <ul class="result-list">
                <?php foreach ($followedArtists as $artist): ?>
                    <li><a href="artist-profile.php?id=<?= (int) $artist['id'] ?>"><?= e($artist['name']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="tab-panel" data-panel="scheduled"<?= $activeTab === 'scheduled' ? '' : ' hidden' ?>>
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

        <div class="tab-panel" data-panel="artworks"<?= $activeTab === 'artworks' ? '' : ' hidden' ?>>
            <div class="panel-head">
                <p>Artworks you posted. New ones are checked by an admin before they appear on the New page.</p>
                <button class="btn" type="button" data-open-dialog="post-artwork">+ Post artwork</button>
            </div>
            <?php if ($myArtworks): ?>
                <div class="grid-3 up-grid">
                    <?php foreach ($myArtworks as $mine): ?>
                        <article class="my-art">
                            <div class="media r-53"><?= render_image((string) $mine['image'], $mine['title']) ?></div>
                            <div class="art-meta">
                                <strong><?= e($mine['title']) ?></strong>
                                <span class="pill pill-<?= e($mine['status']) ?>"><?= e(status_label($mine['status'])) ?></span>
                            </div>
                            <div class="my-art-actions">
                                <?php if ($mine['status'] === 'published'): ?>
                                    <a class="link-small" href="artwork-detail.php?id=<?= (int) $mine['id'] ?>">View</a>
                                <?php elseif ($mine['status'] === 'draft'): ?>
                                    <form method="post" action="profile-action.php">
                                        <?= csrf_field() ?><input type="hidden" name="action" value="resubmit_artwork"><input type="hidden" name="id" value="<?= (int) $mine['id'] ?>">
                                        <button class="link-small" type="submit" title="An admin asked for changes. Send it for review again.">Resubmit</button>
                                    </form>
                                <?php endif; ?>
                                <form method="post" action="profile-action.php" data-confirm="Delete &ldquo;<?= e($mine['title']) ?>&rdquo;? This can't be undone.">
                                    <?= csrf_field() ?><input type="hidden" name="action" value="delete_my_artwork"><input type="hidden" name="id" value="<?= (int) $mine['id'] ?>">
                                    <button class="link-delete" type="submit">Delete</button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="empty-state">You haven't posted any artworks yet.</p>
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

<!-- Edit profile pop-up -->
<dialog id="edit-profile" class="modal" aria-labelledby="edit-profile-title">
    <form method="post" action="profile-action.php" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="update_profile">
        <h2 id="edit-profile-title">Edit profile</h2>

        <div class="avatar-edit">
            <span class="avatar-preview" data-avatar-preview><?= avatar_html($user) ?></span>
            <div>
                <label for="avatar">Profile picture</label>
                <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/webp,image/gif" data-avatar-input>
                <small>JPG, PNG, WEBP or GIF, up to 5 MB.</small>
            </div>
        </div>

        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?= e($user['username']) ?>" minlength="3" maxlength="30" pattern="[A-Za-z0-9_]{3,30}" required>
        <small>3-30 letters, numbers or underscore.</small>

        <div class="modal-actions">
            <button type="button" class="btn-ghost" data-close-dialog>Cancel</button>
            <button type="submit" class="btn-black-sm">Save changes</button>
        </div>
    </form>
</dialog>

<!-- Post artwork pop-up -->
<dialog id="post-artwork" class="modal" aria-labelledby="post-artwork-title">
    <form method="post" action="profile-action.php" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="post_artwork">
        <h2 id="post-artwork-title">Post your artwork</h2>
        <p class="modal-note"><?= is_admin() ? 'As an admin, your artwork is published straight away.' : 'An admin checks every new artwork before it appears on the New page.' ?> It will be shown under your name.</p>

        <label for="art-picture">Picture</label>
        <input type="file" id="art-picture" name="picture" accept="image/jpeg,image/png,image/webp,image/gif" required>
        <small>JPG, PNG, WEBP or GIF, up to 5 MB. A picture can only be used once on Artspace.</small>

        <label for="art-title">Title</label>
        <input type="text" id="art-title" name="title" maxlength="120" required>

        <div class="modal-row">
            <div><label for="art-medium">Medium</label><input type="text" id="art-medium" name="medium" maxlength="60" placeholder="e.g. Oil on canvas"></div>
            <div><label for="art-size">Size</label><input type="text" id="art-size" name="size" maxlength="40" placeholder="e.g. 1920 x 1080"></div>
            <div><label for="art-year">Year</label><input type="text" id="art-year" name="year" maxlength="30" placeholder="e.g. 2026"></div>
        </div>

        <label for="art-desc">Description</label>
        <textarea id="art-desc" name="description" rows="4" maxlength="2000"></textarea>

        <div class="modal-actions">
            <button type="button" class="btn-ghost" data-close-dialog>Cancel</button>
            <button type="submit" class="btn-black-sm">Post artwork</button>
        </div>
    </form>
</dialog>

<dialog id="confirm-dialog" class="modal modal-small" aria-labelledby="confirm-title">
    <h2 id="confirm-title">Are you sure?</h2>
    <p id="confirm-text"></p>
    <div class="modal-actions">
        <button type="button" class="btn-ghost" data-confirm-no>No, cancel</button>
        <button type="button" class="btn-danger" data-confirm-yes>Yes, delete</button>
    </div>
</dialog>

<?php require __DIR__ . '/../includes/footer.php'; ?>
