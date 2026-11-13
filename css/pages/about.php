<?php
// About Us page (opened from the footer). Not in the header menu, so no underline.
// Change the words below.
require_once __DIR__ . '/../includes/init.php';
$pageTitle  = 'About Us';
$activePage = '';
require __DIR__ . '/../includes/header.php';
?>

<div class="page-title center">
    <h1>About us</h1>
</div>

<section class="info-rows">
    <div class="info-row">
        <h2>Our Mission</h2>
        <div class="info-body">
            <p>Our mission is to curate transformative exhibitions that challenge perspectives, empower emerging talent, and design accessible, immersive spatial experiences. We are dedicated to fostering continuous artistic growth by offering dedicated residencies and building strategic creative partnerships.</p>
        </div>
    </div>

    <div class="info-row">
        <h2>Our Vision</h2>
        <div class="info-body">
            <p>Our vision is to be a leading contemporary platform that bridges global artistic innovation with local cultural dialogue. To achieve this, we curate transformative exhibitions that challenge perspectives and empower emerging talent, design accessible spatial experiences for art enthusiasts, and foster continuous artistic growth through dedicated residencies and strategic creative partnerships.</p>
        </div>
    </div>

    <div class="info-row">
        <h2>Our Values</h2>
        <div class="info-body">
            <p>Like a vibrant palette, our core values are shaped by a rich spectrum of diversity, the bold pigments of creative courage, and the chromatic harmony of human connection. We honor every hue of artistic expression, turning our space into a living canvas where diverse ideas blend and thrive.</p>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
