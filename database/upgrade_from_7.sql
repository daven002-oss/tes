-- ============================================================
-- VELA Art Space - UPGRADE for people who already imported the database from "Vela artspace#7".
-- Keeps all your data (users, likes, follows, comments...). Run it ONCE in phpMyAdmin:
--   pick the database "vela_artspace" -> Import -> choose this file.
-- (If you are happy to start fresh, import vela_artspace.sql instead - that resets everything.)
-- ============================================================
USE vela_artspace;
SET NAMES utf8mb4;

-- Edit profile / post your own artworks
ALTER TABLE users
    ADD COLUMN artist_id INT UNSIGNED NULL,
    ADD UNIQUE KEY uq_users_artist (artist_id),
    ADD CONSTRAINT fk_users_artist FOREIGN KEY (artist_id) REFERENCES artists (id) ON DELETE SET NULL;

-- Review flow: only 'published' artworks are shown on the website
ALTER TABLE artworks
    ADD COLUMN status ENUM('draft','in_review','published') NOT NULL DEFAULT 'published',
    ADD COLUMN submitted_by INT UNSIGNED NULL,
    ADD KEY idx_artworks_status (status),
    ADD CONSTRAINT fk_artworks_submitter FOREIGN KEY (submitted_by) REFERENCES users (id) ON DELETE SET NULL;

-- The same uploaded image can't be posted twice
ALTER TABLE pictures
    ADD COLUMN file_hash CHAR(64) NULL,
    ADD UNIQUE KEY uq_pictures_hash (file_hash);

-- Admin inbox
CREATE TABLE messages (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    sender_name    VARCHAR(80)  NOT NULL,
    sender_user_id INT UNSIGNED NULL,
    subject        VARCHAR(120) NOT NULL,
    body           TEXT         NOT NULL,
    artwork_id     INT UNSIGNED NULL,
    is_read        TINYINT(1)   NOT NULL DEFAULT 0,
    is_ignored     TINYINT(1)   NOT NULL DEFAULT 0,
    created_at     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_messages_created (created_at),
    CONSTRAINT fk_messages_user    FOREIGN KEY (sender_user_id) REFERENCES users (id)    ON DELETE SET NULL,
    CONSTRAINT fk_messages_artwork FOREIGN KEY (artwork_id)     REFERENCES artworks (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE message_replies (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    message_id INT UNSIGNED NOT NULL,
    admin_id   INT UNSIGNED NULL,
    body       TEXT         NOT NULL,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_replies_message (message_id),
    CONSTRAINT fk_replies_message FOREIGN KEY (message_id) REFERENCES messages (id) ON DELETE CASCADE,
    CONSTRAINT fk_replies_admin   FOREIGN KEY (admin_id)   REFERENCES users (id)    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Demo submissions and messages (delete them in the admin pages whenever you like)
INSERT INTO artworks (title, artist_id, placement, size, medium, year_text, location, price, description, status)
SELECT 'Sunrise Study', id, 'new', '1920 x 1080', 'Digital art', '2026', 'Jakarta', '$500 USD', 'A demo submission waiting for the admin to review it.', 'in_review'
  FROM artists WHERE name = 'venglish viera';
INSERT INTO artworks (title, artist_id, placement, size, medium, year_text, location, price, description, status)
SELECT 'Quiet Pines', id, 'new', '1920 x 1080', 'Digital art', '2026', 'Jakarta', '$500 USD', 'A demo draft: changes were requested by the admin.', 'draft'
  FROM artists WHERE name = 'maldini zidane';
INSERT INTO messages (sender_name, subject, body, is_read, created_at) VALUES
('Nadia Putri', 'Artwork availability', 'Hi VELA, I came across Golden lake on your website. Is the artwork still on show? I would love to know more about it and how to arrange a gallery visit.', 0, NOW() - INTERVAL 2 HOUR),
('Makoto', 'Artist application', 'Thank you for reviewing my profile.', 1, NOW() - INTERVAL 3 HOUR),
('venglish viera', 'Artwork submission', 'I have uploaded the artwork details.', 1, NOW() - INTERVAL 1 DAY),
('D0ria', 'Artwork update', 'The updated image is now attached.', 1, NOW() - INTERVAL 2 DAY),
('VELA Community', 'Gallery visit', 'Thank you for the information.', 1, NOW() - INTERVAL 3 DAY);
