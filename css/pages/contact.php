<?php
// Contact Us page (opened from the footer). Not in the header menu, so no underline.
// Change the details below.
require_once __DIR__ . '/../includes/init.php';
$pageTitle  = 'Contact Us';
$activePage = '';
require __DIR__ . '/../includes/header.php';
?>

<div class="page-title center">
    <h1>Contact us</h1>
</div>

<section class="info-rows">
    <div class="info-row">
        <h2>Our number</h2>
        <div class="info-body">
            <p>Support</p>
            <p>+62-890-679-1234</p>
        </div>
    </div>

    <div class="info-row">
        <h2>Social media</h2>
        <div class="info-body info-cols">
            <div>
                <p>Instagram</p>
                <p>@velaArts</p>
                <p>Tiktok</p>
                <p>@VelaArts</p>
            </div>
            <div>
                <p>X</p>
                <p>@VelaArts</p>
                <p>Facebook</p>
                <p>@VelaArtspace</p>
            </div>
        </div>
    </div>

    <div class="info-row">
        <h2>Our Email</h2>
        <div class="info-body">
            <p>Business</p>
            <p>VelaArts@gmail.com</p>
            <p>VeleArts@outlook.com</p>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
