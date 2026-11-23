<?php
/*
 * VELA - content loaded from the database.
 *
 * The pages use the lists below ($artists, $artworks, $showcases, $events).
 * To change words, artists or pictures, edit the database (phpMyAdmin) - not this file.
 * Pictures live in the `pictures` table: each picture file can be used only once on the whole site.
 */

// ---------- Home page hero slides (the big picture) ----------
// Not in the database yet. Put real pictures here, e.g. 'image' => 'images/hero/slide1.jpg'
$heroSlides = [
    ['image' => '', 'alt' => 'Hero slide 1'],
    ['image' => '', 'alt' => 'Hero slide 2'],
    ['image' => '', 'alt' => 'Hero slide 3'],
];

$pdo = db();

// ---------- Artists ----------
// 'featured' artists are the ones on the Artists ("Top Artists") page.
$artists = [];
$rows = $pdo->query(
    "SELECT a.id, a.name, a.joined_on, a.is_featured, thumb.path AS thumb, prof.path AS profile,
            (SELECT COUNT(*) FROM artist_follows f WHERE f.artist_id = a.id) AS followers
       FROM artists a
       LEFT JOIN pictures thumb ON thumb.owner_type = 'artist_thumb'   AND thumb.owner_id = a.id
       LEFT JOIN pictures prof  ON prof.owner_type  = 'artist_profile' AND prof.owner_id  = a.id
      ORDER BY a.id"
)->fetchAll();
foreach ($rows as $r) {
    $artists[] = [
        'id'            => (int) $r['id'],
        'name'          => $r['name'],
        'joined'        => date('d/m/Y', strtotime($r['joined_on'])),
        'followers'     => (int) $r['followers'],
        'featured'      => (bool) $r['is_featured'],
        'image'         => (string) $r['thumb'],
        'profile_image' => (string) $r['profile'],
    ];
}

// ---------- Artworks ----------
// section 'curated' = Home page scroller, 'new' = New page. Every artwork has an artist.
$artworks = [];
$rows = $pdo->query(
    "SELECT w.*, a.name AS artist_name, pic.path AS image
       FROM artworks w
       JOIN artists a ON a.id = w.artist_id
       LEFT JOIN pictures pic ON pic.owner_type = 'artwork' AND pic.owner_id = w.id
      ORDER BY w.id"
)->fetchAll();
foreach ($rows as $r) {
    $artworks[] = [
        'id'          => (int) $r['id'],
        'section'     => $r['placement'] === 'home' ? 'curated' : 'new',
        'title'       => $r['title'],
        'artist'      => $r['artist_name'],
        'artist_id'   => (int) $r['artist_id'],
        'image'       => (string) $r['image'],
        'size'        => $r['size'],
        'medium'      => $r['medium'],
        'year'        => $r['year_text'],
        'location'    => $r['location'],
        'price'       => $r['price'],
        'description' => (string) $r['description'],
    ];
}

// ---------- Artwork showcase ----------
$showcases = [];
$rows = $pdo->query(
    "SELECT s.*, a.name AS artist_name, pic.path AS image
       FROM showcases s
       JOIN artists a ON a.id = s.artist_id
       LEFT JOIN pictures pic ON pic.owner_type = 'showcase' AND pic.owner_id = s.id
      ORDER BY s.id"
)->fetchAll();
foreach ($rows as $r) {
    $showcases[] = [
        'id'          => (int) $r['id'],
        'title'       => $r['title'],
        'by'          => $r['artist_name'],
        'artist_id'   => (int) $r['artist_id'],
        'image'       => (string) $r['image'],
        'location'    => $r['location'],
        'dates'       => $r['dates'],
        'description' => (string) $r['description'],
    ];
}

// ---------- Artspace events ----------
$events = [];
$rows = $pdo->query(
    "SELECT e.*, pic.path AS image
       FROM events e
       LEFT JOIN pictures pic ON pic.owner_type = 'event' AND pic.owner_id = e.id
      ORDER BY e.id"
)->fetchAll();
foreach ($rows as $r) {
    $events[] = [
        'id'          => (int) $r['id'],
        'location'    => $r['location'],
        'title'       => $r['title'],
        'image'       => (string) $r['image'],
        'date'        => $r['date_text'],
        'time'        => $r['time_text'],
        'venue'       => $r['venue'],
        'description' => (string) $r['description'],
    ];
}
