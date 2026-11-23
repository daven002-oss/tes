# VELA Art Space - wireframe

PHP + HTML + CSS + JavaScript site with a MySQL database (Laragon / phpMyAdmin).

## Set up the database (phpMyAdmin, Laragon)

1. Start Laragon (Apache + MySQL). Put this folder in `D:\laragon\www\Projek`.
2. Open phpMyAdmin (`http://localhost/phpmyadmin`, user `root`, empty password).
3. Click **Import** -> choose `database/vela_artspace.sql` -> **Import**. This creates the database `vela_artspace` with every table and the starting content.
   (Importing again resets everything back to the starting content.)
4. Open `http://localhost/Projek/pages/home.php`.

If your MySQL uses another user, password or port, change `includes/db_config.php`.
Without phpMyAdmin you can also run `php database/install.php` from this folder.

Demo logins (change or delete them before the site goes online):

| Account | Email | Password |
|---|---|---|
| Admin | `admin@vela.test` | `Admin1234` |
| Normal user (Alya) | `alya@vela.test` | `User1234` |

New people can register on the Sign up page.

## The database rules

- **One picture, one place.** Every picture file is a row in `pictures`, and its `path` is UNIQUE, so the same file can never be used on the home page, New page or any other page. Each item has only one picture row too.
- **Every artwork has an artist.** `artworks.artist_id` is NOT NULL and points to `artists`. An artist who still has artworks can't be deleted. Showcases follow the same rule.
- **Like** an artwork or showcase (the heart) -> saved in `artwork_likes` / `showcase_likes` -> shown in **Saved artworks** on your profile.
- **Follow** an artist -> saved in `artist_follows` -> shown in **Following** on your profile.
- **Schedule** an event -> saved in `event_schedules` -> shown in **Scheduled events** on your profile.
- Comments are saved in `comments`. Passwords are stored hashed.

## Run without Laragon

    php -S localhost:8000

(needs MySQL running and the database imported). Open http://localhost:8000.

## Folders

| Folder | What is in it |
|---|---|
| `pages/` | One PHP file per page (see the list below), plus `api.php` (used by the heart / Follow / Schedule buttons) |
| `includes/` | Shared header and footer, settings (`config.php`), database (`db_config.php`, `db.php`), content loaded from the database (`data.php`), helpers (`functions.php`), login (`auth.php`) |
| `css/` | `style.css` |
| `js/` | `main.js` (hero slider, header search, like buttons, follow button) |
| `images/` | The pictures: `artists/`, `profiles/`, `home/`, `new/`, `showcase/`, `events/`. Their file names are stored in the `pictures` table |
| `database/` | `vela_artspace.sql` (import this in phpMyAdmin), plus `schema.sql`, `seed.sql` and `install.php` |

## Pages

| Page | File | Header underline |
|---|---|---|
| Home | `home.php` | Home |
| New (newly released artworks) | `new.php` | New |
| Artists | `artists.php` | Artists |
| Artwork showcase | `showcase.php` | Artwork showcase |
| Artspace Events | `events.php` | Artspace Events |
| Artwork detail (one artwork) | `artwork-detail.php?id=N` | none |
| Showcase detail (one showcase, with comments) | `showcase-detail.php?id=N` | Artwork showcase |
| Artist profile | `artist-profile.php?id=N` | none |
| Event schedule | `event-schedule.php?id=N` | none |
| Login / Sign up | `login.php`, `signup.php` | none |
| Search results | `search.php?q=...` | none |
| About Us / Contact Us / Privacy & Policy | `about.php`, `contact.php`, `privacy.php` | none |
| My profile (logged in only) | `user-profile.php` | none |
| Admin Overview (admins only) | `admin.php` | none |
| Log Out (button on My profile) | `logout.php` | none |

## Things you will want to change

- **Slide speed / scroller speed / logo file / footer picture / social links**: `includes/config.php`
- **Artworks, showcases, artists, events and their pictures**: edit the tables in phpMyAdmin. Save the picture file in `images/` and put its path (e.g. `images/new/new-1.png`) in the `pictures` table. A picture file may only be used once. Add the artist first, then the artwork with that artist's id.
- **Hero slide pictures** (still empty placeholders): `$heroSlides` at the top of `includes/data.php`.
- **Logo**: save it as `images/logo.png` and it replaces the dashed "LOGO" box automatically.
- **Footer background**: save the dark web texture as `images/footer-bg.jpg` and the footer uses it.
- **About / Contact / Privacy words**: edit `pages/about.php`, `pages/contact.php`, `pages/privacy.php`.
- **Header / footer**: `includes/header.php` and `includes/footer.php` - edit once, every page updates.

## How the pages connect

- Header: Logo and Home -> home, New, Artists, Artwork showcase, Artspace Events, Login.
- Search icon: click it and the search box opens. Enter searches, Esc or clicking elsewhere closes it.
- Footer: About Us, Contact Us, Privacy & Policy.
- Home scroller / New picture -> `artwork-detail.php?id=N`. Showcase picture -> `showcase-detail.php?id=N`.
- Artist row / Go to Profile -> `artist-profile.php?id=N`. Event Schedule button -> `event-schedule.php?id=N`.
- Login page "Sign up" -> sign up page. Sign up page "Login" -> login page.

## Logged in / logged out

- Log in on `login.php` (or sign up on `signup.php`). `includes/auth.php` has `is_logged_in()`, `is_admin()` and `current_user()`, all read from the `users` table.
- Logged in, the **Login** button becomes a round button with your profile picture (your initials if you have none). It opens **My profile** (`user-profile.php`).
- My profile: tabs for **Saved artworks**, **Following** and **Scheduled events**. Normal users see **Log Out**; admins (`is_admin = 1`) also see **Admin Overview**.
- Not logged in, pressing a heart / Follow / Schedule button sends you to the login page.
- Showcase and artwork pages get a **Comment...** box for logged-in visitors.
- Still to do: the "Edit profile" button and "Forgot password?" link do nothing yet.
