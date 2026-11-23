-- ============================================================
-- VELA Art Space - database structure (MySQL / MariaDB)
--
-- Import this file first, then seed.sql.
-- (Or just run:  php database/install.php   - it does both.)
--
-- The rules you asked for are built into the tables:
--   * Every artwork (and every showcase) MUST have an artist:
--       artworks.artist_id / showcases.artist_id are NOT NULL and point to artists.id.
--       An artist who still has artworks or showcases cannot be deleted.
--   * A picture can only be used ONCE on the whole website (home, new, showcase, events, artist photos):
--       every picture is a row in `pictures`, and pictures.path is UNIQUE.
--   * Liking an artwork / showcase  -> a row in artwork_likes / showcase_likes   (= "Saved artworks")
--     Following an artist           -> a row in artist_follows                   (= "Following")
--     Scheduling an event           -> a row in event_schedules                  (= "Scheduled events")
-- ============================================================

CREATE DATABASE IF NOT EXISTS vela_artspace CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vela_artspace;

-- Remove old copies first so the file can be imported again (this erases the data!).
DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS event_schedules;
DROP TABLE IF EXISTS artist_follows;
DROP TABLE IF EXISTS showcase_likes;
DROP TABLE IF EXISTS artwork_likes;
DROP TABLE IF EXISTS pictures;
DROP TABLE IF EXISTS events;
DROP TABLE IF EXISTS showcases;
DROP TABLE IF EXISTS artworks;
DROP TABLE IF EXISTS artists;
DROP TABLE IF EXISTS users;

-- ------------------------------------------------------------
-- People who log in
-- ------------------------------------------------------------
CREATE TABLE users (
    id                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username           VARCHAR(30)  NOT NULL,
    email              VARCHAR(120) NOT NULL,
    password_hash      VARCHAR(255) NOT NULL,                 -- never the real password
    display_name       VARCHAR(80)  NOT NULL,
    title              VARCHAR(80)  NOT NULL DEFAULT 'Art enthusiast',
    bio                TEXT         NULL,
    location           VARCHAR(80)  NULL,
    avatar_path        VARCHAR(255) NULL,                     -- e.g. images/users/alya.jpg (NULL = show initials)
    is_admin           TINYINT(1)   NOT NULL DEFAULT 0,       -- 1 = admin (sees "Admin Overview")
    profile_visibility ENUM('private','public') NOT NULL DEFAULT 'private',
    created_at         TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_username (username),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Artists (the people who made the artworks and showcases)
-- ------------------------------------------------------------
CREATE TABLE artists (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name        VARCHAR(80)  NOT NULL,
    joined_on   DATE         NOT NULL,
    is_featured TINYINT(1)   NOT NULL DEFAULT 0,              -- 1 = listed on the "Top Artists" page
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_artists_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Artworks (Home "Curated Selection" and the "New" page)
-- ------------------------------------------------------------
CREATE TABLE artworks (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title       VARCHAR(120) NOT NULL,
    artist_id   INT UNSIGNED NOT NULL,                        -- required: no artwork without an artist
    placement   ENUM('home','new') NOT NULL,                  -- which page shows it
    size        VARCHAR(40)  NOT NULL DEFAULT '',
    medium      VARCHAR(60)  NOT NULL DEFAULT '',
    year_text   VARCHAR(30)  NOT NULL DEFAULT '',
    location    VARCHAR(80)  NOT NULL DEFAULT '',
    price       VARCHAR(40)  NOT NULL DEFAULT '',
    description TEXT         NULL,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_artworks_artist (artist_id),
    KEY idx_artworks_placement (placement),
    CONSTRAINT fk_artworks_artist FOREIGN KEY (artist_id) REFERENCES artists (id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Artwork showcase (the gallery-room pictures)
-- ------------------------------------------------------------
CREATE TABLE showcases (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title       VARCHAR(120) NOT NULL,
    artist_id   INT UNSIGNED NOT NULL,                        -- required: no showcase without an artist
    location    VARCHAR(120) NOT NULL DEFAULT '',
    dates       VARCHAR(120) NOT NULL DEFAULT '',
    description TEXT         NULL,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_showcases_artist (artist_id),
    CONSTRAINT fk_showcases_artist FOREIGN KEY (artist_id) REFERENCES artists (id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Artspace events
-- ------------------------------------------------------------
CREATE TABLE events (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    location    VARCHAR(80)  NOT NULL,
    title       VARCHAR(120) NOT NULL,
    date_text   VARCHAR(80)  NOT NULL DEFAULT 'Date to be announced',
    time_text   VARCHAR(80)  NOT NULL DEFAULT 'Time to be announced',
    venue       VARCHAR(120) NOT NULL DEFAULT 'Venue placeholder',
    description TEXT         NULL,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Pictures. ONE table for every picture on the site.
-- path is UNIQUE, so the same picture can never appear on two different pages (or twice on one).
-- owner_type + owner_id say which artwork / showcase / event / artist the picture belongs to,
-- and (owner_type, owner_id) is UNIQUE too: one picture per item.
--   artist_thumb   = small photo on the Artists page
--   artist_profile = large photo on the artist's profile page
-- ------------------------------------------------------------
CREATE TABLE pictures (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    path       VARCHAR(255) NOT NULL,                         -- e.g. images/new/new-1.png
    owner_type ENUM('artwork','showcase','event','artist_thumb','artist_profile') NOT NULL,
    owner_id   INT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_pictures_path (path),
    UNIQUE KEY uq_pictures_owner (owner_type, owner_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- When an item is deleted its picture row is deleted too (so the picture can be used again).
CREATE TRIGGER trg_artworks_picture_cleanup AFTER DELETE ON artworks
    FOR EACH ROW DELETE FROM pictures WHERE owner_type = 'artwork' AND owner_id = OLD.id;
CREATE TRIGGER trg_showcases_picture_cleanup AFTER DELETE ON showcases
    FOR EACH ROW DELETE FROM pictures WHERE owner_type = 'showcase' AND owner_id = OLD.id;
CREATE TRIGGER trg_events_picture_cleanup AFTER DELETE ON events
    FOR EACH ROW DELETE FROM pictures WHERE owner_type = 'event' AND owner_id = OLD.id;
CREATE TRIGGER trg_artists_picture_cleanup AFTER DELETE ON artists
    FOR EACH ROW DELETE FROM pictures WHERE owner_type IN ('artist_thumb','artist_profile') AND owner_id = OLD.id;

-- ------------------------------------------------------------
-- What each user saved. A row exists = the user did it.
-- ------------------------------------------------------------
-- Heart on an artwork  -> "Saved artworks" on the profile page
CREATE TABLE artwork_likes (
    user_id    INT UNSIGNED NOT NULL,
    artwork_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, artwork_id),
    CONSTRAINT fk_artwork_likes_user    FOREIGN KEY (user_id)    REFERENCES users (id)    ON DELETE CASCADE,
    CONSTRAINT fk_artwork_likes_artwork FOREIGN KEY (artwork_id) REFERENCES artworks (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Heart on a showcase -> also shown in "Saved artworks"
CREATE TABLE showcase_likes (
    user_id     INT UNSIGNED NOT NULL,
    showcase_id INT UNSIGNED NOT NULL,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, showcase_id),
    CONSTRAINT fk_showcase_likes_user     FOREIGN KEY (user_id)     REFERENCES users (id)     ON DELETE CASCADE,
    CONSTRAINT fk_showcase_likes_showcase FOREIGN KEY (showcase_id) REFERENCES showcases (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Follow button on an artist -> "Following" on the profile page
CREATE TABLE artist_follows (
    user_id    INT UNSIGNED NOT NULL,
    artist_id  INT UNSIGNED NOT NULL,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, artist_id),
    CONSTRAINT fk_artist_follows_user   FOREIGN KEY (user_id)   REFERENCES users (id)   ON DELETE CASCADE,
    CONSTRAINT fk_artist_follows_artist FOREIGN KEY (artist_id) REFERENCES artists (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Schedule button on an event -> "Scheduled events" on the profile page
CREATE TABLE event_schedules (
    user_id    INT UNSIGNED NOT NULL,
    event_id   INT UNSIGNED NOT NULL,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, event_id),
    CONSTRAINT fk_event_schedules_user  FOREIGN KEY (user_id)  REFERENCES users (id)  ON DELETE CASCADE,
    CONSTRAINT fk_event_schedules_event FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Comments under an artwork OR a showcase (exactly one of the two is filled in)
-- ------------------------------------------------------------
CREATE TABLE comments (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id     INT UNSIGNED NOT NULL,
    artwork_id  INT UNSIGNED NULL,
    showcase_id INT UNSIGNED NULL,
    body        TEXT         NOT NULL,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_comments_artwork (artwork_id),
    KEY idx_comments_showcase (showcase_id),
    CONSTRAINT fk_comments_user     FOREIGN KEY (user_id)     REFERENCES users (id)     ON DELETE CASCADE,
    CONSTRAINT fk_comments_artwork  FOREIGN KEY (artwork_id)  REFERENCES artworks (id)  ON DELETE CASCADE,
    CONSTRAINT fk_comments_showcase FOREIGN KEY (showcase_id) REFERENCES showcases (id) ON DELETE CASCADE,
    CONSTRAINT chk_comments_one_target CHECK ((artwork_id IS NOT NULL AND showcase_id IS NULL) OR (artwork_id IS NULL AND showcase_id IS NOT NULL))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
