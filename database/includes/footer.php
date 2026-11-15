<?php
// Shared footer - used by every page. Closes the <main> that header.php opened.
$footerBgFile = dirname(__DIR__) . '/' . $footerBg;
$footerStyle  = is_file($footerBgFile) ? ' style="background-image:url(\'../' . e($footerBg) . '\')"' : '';
$thisPage     = basename($_SERVER['SCRIPT_NAME']);

$footerLinks = [
    'about.php'   => 'About Us',
    'contact.php' => 'Contact Us',
    'privacy.php' => 'Privacy &amp; Policy',
];
?>
</main>

<footer class="site-footer"<?= $footerStyle ?>>
    <div class="footer-inner">
        <div class="footer-left">
            <p class="copyright">&copy; <?= date('Y') ?> <?= e($siteName) ?> Indonesia</p>

            <ul class="footer-links">
                <?php foreach ($footerLinks as $file => $label): ?>
                    <li><a href="<?= e($file) ?>"<?= $thisPage === $file ? ' class="current" aria-current="page"' : '' ?>><?= $label ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="footer-socials" aria-label="Social media">
            <a class="soc soc-ig" href="<?= e($socialLinks['instagram']) ?>" aria-label="Instagram">
                <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" aria-hidden="true"><rect x="4" y="4" width="16" height="16" rx="4.5"/><circle cx="12" cy="12" r="3.6"/><circle cx="16.9" cy="7.1" r=".6" fill="#fff"/></svg>
            </a>
            <a class="soc soc-tt" href="<?= e($socialLinks['tiktok']) ?>" aria-label="TikTok">
                <svg viewBox="0 0 24 24" fill="#fff" aria-hidden="true"><path d="M16.6 5.8a4.3 4.3 0 0 1-1-2.8h-3v12.4a2.6 2.6 0 1 1-2.6-2.6c.3 0 .5 0 .8.1V9.8a5.7 5.7 0 1 0 4.9 5.6V9.2a7.3 7.3 0 0 0 4.2 1.3V7.5a4.3 4.3 0 0 1-3.3-1.7z"/></svg>
            </a>
            <a class="soc soc-fb" href="<?= e($socialLinks['facebook']) ?>" aria-label="Facebook">
                <svg viewBox="0 0 24 24" fill="#fff" aria-hidden="true"><path d="M13.5 22v-8h2.7l.5-3.3h-3.2V8.6c0-.9.4-1.7 1.8-1.7h1.5V4.1c-.3 0-1.3-.2-2.4-.2-2.5 0-4.1 1.5-4.1 4.2v2.6H7.5V14h2.8v8h3.2z"/></svg>
            </a>
            <a class="soc soc-x" href="<?= e($socialLinks['x']) ?>" aria-label="X">
                <svg viewBox="0 0 24 24" fill="#fff" aria-hidden="true"><path d="M18.2 2.5h3.1l-6.8 7.7 8 10.6h-6.2l-4.9-6.4-5.6 6.4H2.7l7.3-8.3L2.4 2.5h6.4l4.4 5.8 5-5.8zm-1.1 16.4h1.7L7.4 4.3H5.5l11.6 14.6z"/></svg>
            </a>
        </div>
    </div>
</footer>

<script src="../js/main.js"></script>
</body>
</html>
