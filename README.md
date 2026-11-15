# VELA Art Space - wireframe

PHP + HTML + CSS + JavaScript wireframe. No database yet.

## Run it

From inside this folder:

    php -S localhost:8000

Then open http://localhost:8000 (it redirects to the home page).
Or copy the whole folder into Laragon/XAMPP `www`/`htdocs` and open `/<folder>/pages/home.php`.

## Folders

| Folder | What is in it |
|---|---|
| `pages/` | One PHP file per page (see the list below) |
| `includes/` | Shared header and footer, settings (`config.php`), content (`data.php`), helpers (`functions.php`) |
| `css/` | `style.css` |
| `js/` | `main.js` (hero slider, header search, like buttons, follow button) |
| `images/` | Put real pictures here. `images/placeholder/` is spare |
| `database/` | Empty - reserved for the database later |

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
- **All words and pictures** (artworks, showcases, artists, events, hero slides): `includes/data.php`
  - `'image' => ''` shows a grey placeholder box.
  - To use a real picture, save it in `images/` and set e.g. `'image' => 'images/artworks/art1.jpg'`.
  - Hero slide pictures are the `$heroSlides` list at the top.
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

- `includes/auth.php` has `is_logged_in()`, `is_admin()` and `current_user()`. Nobody can log in until the database exists, so for now the pages below can't be reached (they send you to the login page).
- Logged in, the **Login** button becomes a round button with your profile picture (your initials if you have no picture). It opens **My profile** (`user-profile.php`).
- My profile: normal users see **Log Out**. Admins also see **Admin Overview**.
- Showcase and artwork pages get a **Comment...** box for logged-in visitors.
- When the database is ready, make `login.php` set these after a correct password and everything switches on by itself:

      $_SESSION['user_id']  = $userRow['id'];
      $_SESSION['is_admin'] = (bool) $userRow['is_admin'];

- The person's details (name, email, bio, saved artworks...) are placeholders in `current_user()` in `includes/auth.php`. Replace them with a database lookup. A profile picture is set with `'avatar' => 'images/users/name.jpg'`.
- Comments appear on every artwork page and every showcase page. The layout is shared in `includes/comments.php`; the placeholder text is `$defaultComments` in `includes/data.php`.
