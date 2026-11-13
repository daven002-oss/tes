/*
 * VELA - small behaviours used across pages.
 * The home page slide timing is set in includes/config.php ($heroSlideInterval).
 */
document.addEventListener('DOMContentLoaded', function () {

    // ---------- Home hero slider (auto-changing picture) ----------
    document.querySelectorAll('.hero-slider').forEach(function (slider) {
        var slides = slider.querySelectorAll('.slide');
        var dots = slider.querySelectorAll('.dot');
        var interval = parseInt(slider.getAttribute('data-interval'), 10) || 4000;
        var current = 0;
        var timer = null;

        if (slides.length < 2) return;

        function show(index) {
            current = (index + slides.length) % slides.length;
            slides.forEach(function (s, i) { s.classList.toggle('active', i === current); });
            dots.forEach(function (d, i) { d.classList.toggle('active', i === current); });
        }

        function start() {
            timer = setInterval(function () { show(current + 1); }, interval);
        }

        dots.forEach(function (dot, i) {
            dot.addEventListener('click', function () {
                clearInterval(timer);
                show(i);
                start();
            });
        });

        start();
    });

    // ---------- Header search: the magnifier opens the search box ----------
    document.querySelectorAll('.search').forEach(function (form) {
        var btn = form.querySelector('.search-btn');
        var input = form.querySelector('input');

        function setOpen(open) {
            form.classList.toggle('open', open);
            var header = form.closest('.site-header');
            if (header) header.classList.toggle('search-open', open);
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (open) input.focus();
        }

        btn.addEventListener('click', function () {
            if (!form.classList.contains('open')) {
                setOpen(true);                       // closed -> open it
            } else if (input.value.trim() !== '') {
                form.submit();                       // open + typed something -> search
            } else {
                setOpen(false);                      // open + empty -> close it
            }
        });

        input.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                setOpen(false);
                btn.focus();
            }
        });

        // Click anywhere else on the page: close the box if it is empty.
        document.addEventListener('click', function (event) {
            if (!form.contains(event.target) && input.value.trim() === '') {
                setOpen(false);
            }
        });

        form.addEventListener('submit', function (event) {
            if (input.value.trim() === '') event.preventDefault();
        });
    });

    // ---------- Heart (like) buttons ----------
    document.querySelectorAll('.heart').forEach(function (btn) {
        btn.addEventListener('click', function (event) {
            event.preventDefault();
            var on = btn.getAttribute('aria-pressed') !== 'true';
            btn.setAttribute('aria-pressed', on ? 'true' : 'false');
            btn.classList.toggle('liked', on);
            btn.innerHTML = on ? '&#9829;' : '&#9825;';
        });
    });

    // ---------- Follow button on artist profile ----------
    document.querySelectorAll('[data-follow]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var on = btn.classList.toggle('following');
            btn.textContent = on ? 'Following' : 'Follow';
        });
    });
});
