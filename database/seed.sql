-- ============================================================
-- VELA Art Space - starting content. Import AFTER schema.sql.
--
-- DEMO ACCOUNTS (change or delete these before the site goes public!):
--   admin@vela.test   password: Admin1234   (admin)
--   alya@vela.test    password: User1234    (normal user, has a few saved items so the profile page isn't empty)
-- ============================================================

USE vela_artspace;
SET NAMES utf8mb4;

-- Users
INSERT INTO users (id, username, email, password_hash, display_name, title, bio, location, is_admin) VALUES
(1, 'admin', 'admin@vela.test', '$2y$10$YtUzBmHv8doSMI6c.nSjN.SNDQtXLDxk4iXT.6ACuS19S9vpKz7s.', 'VELA Admin', 'Administrator', NULL, 'Jakarta, Indonesia', 1),
(2, 'alyaputri', 'alya@vela.test', '$2y$10$D..BSQ7fsMUjTP.3mvZ72OlX9Hip0.M0vsp914oxY6cthJOC8iW0O', 'Alya Putri', 'Art enthusiast', 'Finding inspiration in everyday colours, quiet landscapes, and the artists who see things a little differently.', 'Jakarta, Indonesia', 0),
(3, 'ralphedwards', 'ralph@vela.test', '$2y$10$VjCoWe9IZjbAYmWy./fEi.2FihpilQ3GZeAZOxMUCbaMpsVXTFFJq', 'Ralph Edwards', 'Art enthusiast', NULL, NULL, 0);

-- Artists (the first four are the 'Top Artists')
INSERT INTO artists (id, name, joined_on, is_featured) VALUES
(1, 'Makoto', '2019-02-22', 1),
(2, 'Se4r', '2019-10-31', 1),
(3, 'G1ORia', '2022-12-06', 1),
(4, 'D0ria', '2018-01-04', 1),
(5, 'Slnae', '2024-01-01', 0),
(6, 'venglish viera', '2024-01-01', 0),
(7, 'maldini zidane', '2024-01-01', 0),
(8, 'Gattuso', '2024-01-01', 0),
(9, 'chandra', '2024-01-01', 0),
(10, 'Artist name', '2024-01-01', 0),
(11, 'ewwiieess', '2024-01-01', 0),
(12, 'jeomin1101', '2024-01-01', 0),
(13, 'thuglifebees', '2024-01-01', 0),
(14, 'frede', '2024-01-01', 0),
(15, 'wen chai fa', '2024-01-01', 0),
(16, 'martin', '2024-01-01', 0),
(17, 'erviana', '2024-01-01', 0),
(18, 'awang', '2024-01-01', 0),
(19, 'Aisha Rahman', '2024-01-01', 0),
(20, 'Kenji Tanaka', '2024-01-01', 0),
(21, 'Chloe Dublos', '2024-01-01', 0),
(22, 'David Chen', '2024-01-01', 0),
(23, 'Isabelia Rossi', '2024-01-01', 0),
(24, 'deross', '2024-01-01', 0);

-- Artworks (placement 'home' = Home page Curated Selection, 'new' = New page)
INSERT INTO artworks (id, title, artist_id, placement, size, medium, year_text, location, price, description) VALUES
(1, 'floweR-mAN', 5, 'home', '1920 x 1080', 'Digital art', '2024', 'Los Angeles', '$1,000 USD', 'Placeholder description for this artwork. Change this text in the database.'),
(2, 'The phandom', 6, 'home', '1920 x 1080', 'Digital art', '2024', 'Los Angeles', '$1,000 USD', 'Placeholder description for this artwork. Change this text in the database.'),
(3, 'Vangush', 4, 'home', '1920 x 1080', 'Digital art', '1999 - 2009', 'Los Angeles', '$10,000 USD', 'Placeholder description for this artwork. Change this text in the database.'),
(4, 'St mount', 7, 'home', '1920 x 1080', 'Digital art', '2024', 'Los Angeles', '$1,000 USD', 'Placeholder description for this artwork. Change this text in the database.'),
(5, 'Monolith', 8, 'home', '1920 x 1080', 'Digital art', '2024', 'Los Angeles', '$1,000 USD', 'Placeholder description for this artwork. Change this text in the database.'),
(6, 'Ephemeral', 9, 'home', '1920 x 1080', 'Digital art', '2024', 'Los Angeles', '$1,000 USD', 'Placeholder description for this artwork. Change this text in the database.'),
(7, 'Artwork Title', 10, 'home', '1920 x 1080', 'Digital art', '2024', 'Los Angeles', '$1,000 USD', 'Placeholder description for this artwork. Change this text in the database.'),
(8, 'Golden lake', 1, 'new', '1920 x 1080', 'Digital art', '2024', 'Los Angeles', '$1,000 USD', 'Placeholder description for this artwork. Change this text in the database.'),
(9, 'Messiah', 11, 'new', '1920 x 1080', 'Digital art', '2024', 'Los Angeles', '$1,000 USD', 'Placeholder description for this artwork. Change this text in the database.'),
(10, 'Cliffs', 12, 'new', '1920 x 1080', 'Digital art', '2024', 'Los Angeles', '$1,000 USD', 'Placeholder description for this artwork. Change this text in the database.'),
(11, 'Acacia', 13, 'new', '1920 x 1080', 'Digital art', '2024', 'Los Angeles', '$1,000 USD', 'Placeholder description for this artwork. Change this text in the database.'),
(12, 'Mount', 14, 'new', '1920 x 1080', 'Digital art', '2024', 'Los Angeles', '$1,000 USD', 'Placeholder description for this artwork. Change this text in the database.'),
(13, 'Green Hills', 15, 'new', '1920 x 1080', 'Digital art', '2024', 'Los Angeles', '$1,000 USD', 'Placeholder description for this artwork. Change this text in the database.'),
(14, 'Grey Hills', 16, 'new', '1920 x 1080', 'Digital art', '2024', 'Los Angeles', '$1,000 USD', 'Placeholder description for this artwork. Change this text in the database.'),
(15, 'Green Pasture', 17, 'new', '1920 x 1080', 'Digital art', '2024', 'Los Angeles', '$1,000 USD', 'Placeholder description for this artwork. Change this text in the database.'),
(16, 'Dusk Mountain', 18, 'new', '1920 x 1080', 'Digital art', '2024', 'Los Angeles', '$1,000 USD', 'Placeholder description for this artwork. Change this text in the database.');

-- Showcases (every showcase has an artist)
INSERT INTO showcases (id, title, artist_id, location, dates, description) VALUES
(1, 'The Red Hall', 19, 'Gallery location placeholder', 'Dates to be announced', 'A grand alpine panorama showcasing majestic, jagged mountain ridges catching the final blazing orange rays of a sunset. The deep, dark foreground of a dense evergreen forest wraps around the edges of a calm mountain lake, contrasting heavily with the fiery violet and gold clouds exploding across the sky.'),
(2, 'Perspective Shift', 20, 'Gallery location placeholder', 'Dates to be announced', 'Placeholder description for this showcase. Change this text in the database.'),
(3, 'The Golden Age', 21, 'Gallery location placeholder', 'Dates to be announced', 'Placeholder description for this showcase. Change this text in the database.'),
(4, 'Salon Style', 22, 'Gallery location placeholder', 'Dates to be announced', 'Placeholder description for this showcase. Change this text in the database.'),
(5, 'The Atrium', 23, 'Gallery location placeholder', 'Dates to be announced', 'Placeholder description for this showcase. Change this text in the database.'),
(6, 'The skyfall', 24, 'Gallery location placeholder', 'Dates to be announced', 'Placeholder description for this showcase. Change this text in the database.'),
(7, 'ghost Style', 22, 'Gallery location placeholder', 'Dates to be announced', 'Placeholder description for this showcase. Change this text in the database.'),
(8, 'The hallway', 23, 'Gallery location placeholder', 'Dates to be announced', 'Placeholder description for this showcase. Change this text in the database.'),
(9, 'Datrium', 23, 'Gallery location placeholder', 'Dates to be announced', 'Placeholder description for this showcase. Change this text in the database.');

-- Events
INSERT INTO events (id, location, title, description) VALUES
(1, 'New Zealand', 'Endless Pastures', 'A sweeping landscape seemingly endless rows of beautifully drawn and painted art.'),
(2, 'London', 'St. Mary River', 'A grand alpine panorama showcasing majestic, jagged mountain ridges mirrored in a calm river.'),
(3, 'City name', 'Event Title 3', 'Placeholder description for the third event. Change this text in the database.');

-- Pictures: each file can be listed only once, on the whole site (pictures.path is UNIQUE)
INSERT INTO pictures (path, owner_type, owner_id) VALUES
('images/artists/artist-1.png', 'artist_thumb', 1),
('images/artists/artist-2.png', 'artist_thumb', 2),
('images/artists/artist-3.png', 'artist_thumb', 3),
('images/artists/artist-4.png', 'artist_thumb', 4),
('images/profiles/profile-1.png', 'artist_profile', 1),
('images/profiles/profile-2.png', 'artist_profile', 2),
('images/profiles/profile-3.png', 'artist_profile', 3),
('images/profiles/profile-4.png', 'artist_profile', 4),
('images/home/home-1.png', 'artwork', 1),
('images/home/home-2.png', 'artwork', 2),
('images/home/home-3.png', 'artwork', 3),
('images/home/home-4.png', 'artwork', 4),
('images/home/home-5.png', 'artwork', 5),
('images/home/home-6.png', 'artwork', 6),
('images/home/home-7.png', 'artwork', 7),
('images/new/new-1.png', 'artwork', 8),
('images/new/new-2.png', 'artwork', 9),
('images/new/new-3.png', 'artwork', 10),
('images/new/new-4.png', 'artwork', 11),
('images/new/new-5.png', 'artwork', 12),
('images/new/new-6.png', 'artwork', 13),
('images/new/new-7.png', 'artwork', 14),
('images/new/new-8.png', 'artwork', 15),
('images/new/new-9.png', 'artwork', 16),
('images/showcase/showcase-1.png', 'showcase', 1),
('images/showcase/showcase-2.png', 'showcase', 2),
('images/showcase/showcase-3.png', 'showcase', 3),
('images/showcase/showcase-4.png', 'showcase', 4),
('images/showcase/showcase-5.png', 'showcase', 5),
('images/showcase/showcase-6.png', 'showcase', 6),
('images/showcase/showcase-7.png', 'showcase', 7),
('images/showcase/showcase-8.png', 'showcase', 8),
('images/showcase/showcase-9.png', 'showcase', 9),
('images/events/event-1.png', 'event', 1),
('images/events/event-2.png', 'event', 2),
('images/events/event-3.png', 'event', 3);

-- Demo: what Alya already saved / follows / scheduled (matches the profile page design)
INSERT INTO artwork_likes (user_id, artwork_id) VALUES (2, 8), (2, 9), (2, 10);
INSERT INTO artist_follows (user_id, artist_id) VALUES (2, 1), (2, 2), (2, 3), (2, 4);
INSERT INTO event_schedules (user_id, event_id) VALUES (2, 1), (2, 2);

-- Demo comments on the first showcase
INSERT INTO comments (user_id, showcase_id, body, created_at) VALUES
(3, 1, 'In mauris porttitor tincidunt mauris massa sit lorem sed scelerisque. Fringilla pharetra vel massa enim sollicitudin cras. At pulvinar eget sociis adipiscing eget donec ultricies nibh tristique.', '2021-08-19 10:00:00'),
(3, 1, 'In mauris porttitor tincidunt mauris massa sit lorem sed scelerisque. Fringilla pharetra vel massa enim sollicitudin cras. At pulvinar eget sociis adipiscing eget donec ultricies nibh tristique.', '2021-08-19 11:00:00');
