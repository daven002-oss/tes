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

    // ---------- Buttons that save things (heart, Follow, Schedule, Private/Public) ----------
    // They talk to pages/api.php. Not logged in? You are sent to the login page.
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');

    function callApi(payload) {
        return fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfMeta ? csrfMeta.content : '' },
            body: JSON.stringify(payload)
        }).then(function (response) {
            if (response.status === 401) {
                window.location.href = 'login.php';
                return null;
            }
            return response.json();
        });
    }

    // Heart on an artwork / showcase  ->  "Saved artworks" on your profile
    document.querySelectorAll('.heart[data-like]').forEach(function (btn) {
        btn.addEventListener('click', function (event) {
            event.preventDefault();
            btn.disabled = true;
            callApi({ action: 'toggle_like', type: btn.getAttribute('data-like'), id: Number(btn.getAttribute('data-id')) })
                .then(function (data) {
                    if (data && data.ok) {
                        btn.setAttribute('aria-pressed', data.on ? 'true' : 'false');
                        btn.classList.toggle('liked', data.on);
                        btn.innerHTML = data.on ? '&#9829;' : '&#9825;';
                    }
                })
                .catch(function () {})
                .then(function () { btn.disabled = false; });
        });
    });

    // Follow button on an artist profile  ->  "Following" on your profile
    document.querySelectorAll('[data-follow-artist]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            btn.disabled = true;
            callApi({ action: 'toggle_follow', id: Number(btn.getAttribute('data-follow-artist')) })
                .then(function (data) {
                    if (data && data.ok) {
                        btn.classList.toggle('following', data.on);
                        btn.setAttribute('aria-pressed', data.on ? 'true' : 'false');
                        btn.textContent = data.on ? 'Following' : 'Follow';
                        var count = document.querySelector('[data-followers]');
                        if (count) count.textContent = data.followers;
                    }
                })
                .catch(function () {})
                .then(function () { btn.disabled = false; });
        });
    });

    // Schedule button on an event  ->  "Scheduled events" on your profile
    document.querySelectorAll('[data-schedule]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            btn.disabled = true;
            callApi({ action: 'toggle_schedule', id: Number(btn.getAttribute('data-schedule')) })
                .then(function (data) {
                    if (data && data.ok) {
                        btn.classList.toggle('scheduled', data.on);
                        btn.setAttribute('aria-pressed', data.on ? 'true' : 'false');
                        btn.innerHTML = data.on ? 'Scheduled &#10003;' : 'Schedule';
                    }
                })
                .catch(function () {})
                .then(function () { btn.disabled = false; });
        });
    });

    // ---------- Like buttons on comments ----------
    document.querySelectorAll('.c-like').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var on = btn.getAttribute('aria-pressed') !== 'true';
            btn.setAttribute('aria-pressed', on ? 'true' : 'false');
            btn.classList.toggle('liked', on);
        });
    });

    // ---------- My profile: tabs and Private/Public switch ----------
    document.querySelectorAll('.tabs').forEach(function (tabs) {
        var buttons = tabs.querySelectorAll('.tab');
        buttons.forEach(function (tab) {
            tab.addEventListener('click', function () {
                buttons.forEach(function (t) {
                    var on = t === tab;
                    t.classList.toggle('active', on);
                    t.setAttribute('aria-selected', on ? 'true' : 'false');
                });
                document.querySelectorAll('.tab-panel').forEach(function (panel) {
                    panel.hidden = panel.getAttribute('data-panel') !== tab.getAttribute('data-tab');
                });
            });
        });
    });

    document.querySelectorAll('.toggle').forEach(function (toggle) {
        var options = toggle.querySelectorAll('button');
        options.forEach(function (option) {
            option.addEventListener('click', function () {
                options.forEach(function (o) {
                    o.classList.toggle('on', o === option);
                    o.setAttribute('aria-pressed', o === option ? 'true' : 'false');
                });
                if (option.hasAttribute('data-visibility')) {
                    callApi({ action: 'set_visibility', value: option.getAttribute('data-visibility') });
                }
            });
        });
    });

    // ---------- Pop-ups (Edit profile, Post artwork) ----------
    document.querySelectorAll('[data-open-dialog]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var dialog = document.getElementById(btn.getAttribute('data-open-dialog'));
            if (dialog && dialog.showModal) dialog.showModal();
        });
    });
    document.querySelectorAll('dialog.modal').forEach(function (dialog) {
        dialog.querySelectorAll('[data-close-dialog]').forEach(function (b) {
            b.addEventListener('click', function () { dialog.close(); });
        });
        dialog.addEventListener('click', function (event) {      // click on the dark background closes it
            if (event.target === dialog) dialog.close();
        });
    });

    // Profile picture preview in Edit profile
    var avatarInput = document.querySelector('[data-avatar-input]');
    var avatarPreview = document.querySelector('[data-avatar-preview]');
    if (avatarInput && avatarPreview) {
        avatarInput.addEventListener('change', function () {
            var file = avatarInput.files && avatarInput.files[0];
            if (!file) return;
            var img = document.createElement('img');
            img.alt = '';
            img.src = URL.createObjectURL(file);
            avatarPreview.innerHTML = '';
            avatarPreview.appendChild(img);
        });
    }

    // ---------- "Are you sure?" pop-up for Delete buttons ----------
    // Any <form data-confirm="question"> asks first. Optional: data-confirm-yes (button text),
    // data-confirm-title, data-confirm-tone="neutral" (black button instead of red).
    var confirmDialog = document.getElementById('confirm-dialog');
    var pendingForm = null;
    if (confirmDialog) {
        var yes = confirmDialog.querySelector('[data-confirm-yes]');
        var no = confirmDialog.querySelector('[data-confirm-no]');
        document.querySelectorAll('form[data-confirm]').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (form.dataset.confirmed === '1') return;      // already confirmed: let it go
                event.preventDefault();
                if (!confirmDialog.showModal) {                  // very old browser
                    if (window.confirm(form.getAttribute('data-confirm'))) { form.dataset.confirmed = '1'; form.submit(); }
                    return;
                }
                pendingForm = form;
                confirmDialog.querySelector('#confirm-title').textContent = form.getAttribute('data-confirm-title') || 'Are you sure?';
                confirmDialog.querySelector('#confirm-text').textContent = form.getAttribute('data-confirm');
                yes.textContent = form.getAttribute('data-confirm-yes') || 'Yes, delete';
                yes.className = form.getAttribute('data-confirm-tone') === 'neutral' ? 'btn-black-sm' : 'btn-danger';
                confirmDialog.showModal();
                no.focus();
            });
        });
        no.addEventListener('click', function () { pendingForm = null; confirmDialog.close(); });
        confirmDialog.addEventListener('click', function (event) {
            if (event.target === confirmDialog) { pendingForm = null; confirmDialog.close(); }
        });
        yes.addEventListener('click', function () {
            var form = pendingForm;
            pendingForm = null;
            confirmDialog.close();
            if (form) { form.dataset.confirmed = '1'; form.submit(); }
        });
    }

    // ---------- Admin inbox: filter the list while typing ----------
    var inboxSearch = document.querySelector('[data-inbox-search]');
    if (inboxSearch) {
        inboxSearch.addEventListener('input', function () {
            var term = inboxSearch.value.trim().toLowerCase();
            document.querySelectorAll('.inbox-item').forEach(function (item) {
                item.hidden = term !== '' && item.getAttribute('data-text').indexOf(term) === -1;
            });
        });
    }
});
