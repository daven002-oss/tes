<?php
/*
 * VELA - site settings. Edit the values below.
 */

$siteName = 'VELA';

// Home page hero slider: how long each slide stays on screen, in milliseconds.
// 4000 = 4 seconds, 6000 = 6 seconds, etc.
$heroSlideInterval = 4000;

// Home page "Curated Selection" scroller: seconds for one full loop.
// Bigger number = slower scrolling.
$marqueeSeconds = 40;

// Logo image path, relative to the project root (vela-artspace/).
// While this file does not exist, a dashed "LOGO" placeholder is shown instead.
$logoImage = 'images/logo.png';

// Footer background picture (the dark web texture in the Figma).
// While this file does not exist, a plain dark footer is shown instead.
$footerBg = 'images/footer-bg.jpg';

// Footer social media links. Replace each '#' with the real address.
$socialLinks = [
    'instagram' => '#',
    'tiktok'    => '#',
    'facebook'  => '#',
    'x'         => '#',
];

// The ONE account that may make other users admins (Admin > Users page).
define('SUPER_ADMIN_EMAIL', 'admin@vela.test');
