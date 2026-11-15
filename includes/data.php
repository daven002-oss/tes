<?php
/*
 * VELA - placeholder content.
 *
 * Everything shown on the site comes from the arrays below, so you can change
 * words and pictures here. Later you can replace these arrays with database queries.
 *
 * 'image' => ''  shows a grey placeholder box.
 * To use a real picture, put the file in the images/ folder and set the path
 * relative to the project root, e.g.  'image' => 'images/artworks/art1.jpg'
 */

// ---------- Home page hero slides (the big picture) ----------
$heroSlides = [
    ['image' => '', 'alt' => 'Hero slide 1'],
    ['image' => '', 'alt' => 'Hero slide 2'],
    ['image' => '', 'alt' => 'Hero slide 3'],
];

// ---------- Artists ----------
$artists = [
    ['id' => 1, 'name' => 'Makoto', 'joined' => '22/02/2019', 'followers' => 945, 'following' => 4, 'image' => 'images/artists/artist-1.png', 'profile_image' => 'images/profiles/profile-1.png'],
    ['id' => 2, 'name' => 'Se4r',   'joined' => '31/10/2019', 'followers' => 512, 'following' => 12, 'image' => 'images/artists/artist-2.png', 'profile_image' => 'images/profiles/profile-2.png'],
    ['id' => 3, 'name' => 'G1ORia', 'joined' => '06/12/2022', 'followers' => 230, 'following' => 31, 'image' => 'images/artists/artist-3.png', 'profile_image' => 'images/profiles/profile-3.png'],
    ['id' => 4, 'name' => 'D0ria',  'joined' => '04/01/2018', 'followers' => 1280, 'following' => 8, 'image' => 'images/artists/artist-4.png', 'profile_image' => 'images/profiles/profile-4.png'],
];

// ---------- Artworks ----------
// Short helper so each artwork is one line. Order of the first arguments:
//   id, section, title, artist name, artist id (a number from $artists above, or null)
// section 'curated' = shown in the Home page scroller
// section 'new'     = shown on the New page
// Put extra/changed details in the last argument, e.g. ['price' => '$500 USD'].
function make_art(int $id, string $section, string $title, string $artist, ?int $artistId = null, array $extra = []): array
{
    return array_merge([
        'id'          => $id,
        'section'     => $section,
        'title'       => $title,
        'artist'      => $artist,
        'artist_id'   => $artistId,
        'image'       => '',
        'size'        => '1920 x 1080',
        'medium'      => 'Digital art',
        'year'        => '2024',
        'location'    => 'Los Angeles',
        'price'       => '$1,000 USD',
        'description' => 'Placeholder description for this artwork. Change this text in includes/data.php.',
    ], $extra);
}

$artworks = [
    // Home page - Curated Selection
    make_art(1,  'curated', 'floweR-mAN',    'Slnae',          null, ['image' => 'images/home/home-1.png']),
    make_art(2,  'curated', 'The phandom',   'venglish viera', null, ['image' => 'images/home/home-2.png']),
    make_art(3,  'curated', 'Vangush',       'D0ria', 4, ['image' => 'images/home/home-3.png', 'price' => '$10,000 USD', 'year' => '1999 - 2009']),
    make_art(4,  'curated', 'St mount',      'maldini zidane', null, ['image' => 'images/home/home-4.png']),
    make_art(5,  'curated', 'Monolith',      'Gattuso',        null, ['image' => 'images/home/home-5.png']),
    make_art(6,  'curated', 'Ephemeral',     'chandra',        null, ['image' => 'images/home/home-6.png']),
    make_art(16, 'curated', 'Artwork Title', 'Artist name',    null, ['image' => 'images/home/home-7.png']),   // 7th picture - give it its real title

    // New page
    make_art(7,  'new', 'Golden lake',   'makoto', 1, ['image' => 'images/new/new-1.png']),
    make_art(8,  'new', 'Messiah',       'ewwiieess',    null, ['image' => 'images/new/new-2.png']),
    make_art(9,  'new', 'Cliffs',        'jeomin1101',   null, ['image' => 'images/new/new-3.png']),
    make_art(10, 'new', 'Acacia',        'thuglifebees', null, ['image' => 'images/new/new-4.png']),
    make_art(11, 'new', 'Mount',         'frede',        null, ['image' => 'images/new/new-5.png']),
    make_art(12, 'new', 'Green Hills',   'wen chai fa',  null, ['image' => 'images/new/new-6.png']),
    make_art(13, 'new', 'Grey Hills',    'martin',       null, ['image' => 'images/new/new-7.png']),
    make_art(14, 'new', 'Green Pasture', 'erviana',      null, ['image' => 'images/new/new-8.png']),
    make_art(15, 'new', 'Dusk Mountain', 'awang',        null, ['image' => 'images/new/new-9.png']),
];

// Make sure every artist profile has 3 placeholder artworks to show.
// (These only appear on profile pages. Delete this block once real data exists.)
$placeholderId = 100;
foreach ($artists as $a) {
    $count = 0;
    foreach ($artworks as $w) {
        if ($w['artist_id'] === $a['id']) {
            $count++;
        }
    }
    while ($count < 3) {
        // pictures: images/profile-artworks/profile-artwork-1.png ... -3.png, used in turn
        $pic = 'images/profile-artworks/profile-artwork-' . ($count + 1) . '.png';
        $artworks[] = make_art($placeholderId++, 'profile', 'Artwork Title', $a['name'], $a['id'], ['image' => $pic]);
        $count++;
    }
}

// ---------- Artwork showcase ----------
$showcases = [
    ['id' => 1, 'title' => 'The Red Hall',       'by' => 'Aisha Rahman'],
    ['id' => 2, 'title' => 'Perspective Shift',  'by' => 'Kenji Tanaka'],
    ['id' => 3, 'title' => 'The Golden Age',     'by' => 'Chloe Dublos'],
    ['id' => 4, 'title' => 'Salon Style',        'by' => 'David Chen'],
    ['id' => 5, 'title' => 'The Atrium',         'by' => 'Isabelia Rossi'],
    ['id' => 6, 'title' => 'The skyfall',        'by' => 'deross'],
    ['id' => 7, 'title' => 'ghost Style',        'by' => 'David Chen'],
    ['id' => 8, 'title' => 'The hallway',        'by' => 'Isabelia Rossi'],
    ['id' => 9, 'title' => 'Datrium',            'by' => 'Isabelia Rossi'],
];
// Placeholder comments shown under every showcase.
// To give one showcase its own comments, add a 'comments' => [...] list to it above.
$defaultComments = [
    ['name' => 'Ralph Edwards', 'date' => 'Aug 19, 2021', 'text' => 'In mauris porttitor tincidunt mauris massa sit lorem sed scelerisque. Fringilla pharetra vel massa enim sollicitudin cras. At pulvinar eget sociis adipiscing eget donec ultricies nibh tristique.'],
    ['name' => 'Ralph Edwards', 'date' => 'Aug 19, 2021', 'text' => 'In mauris porttitor tincidunt mauris massa sit lorem sed scelerisque. Fringilla pharetra vel massa enim sollicitudin cras. At pulvinar eget sociis adipiscing eget donec ultricies nibh tristique.'],
];

// The first showcase uses the text from the Figma design.
$showcases[0]['description'] = 'A grand alpine panorama showcasing majestic, jagged mountain ridges catching the final blazing orange rays of a sunset. The deep, dark foreground of a dense evergreen forest wraps around the edges of a calm mountain lake, contrasting heavily with the fiery violet and gold clouds exploding across the sky.';

// Fill in the details every showcase shares (change any of them per showcase above).
foreach ($showcases as $i => $s) {
    $showcases[$i] += [
        'image'       => 'images/showcase/showcase-' . $s['id'] . '.png',
        'location'    => 'Gallery location placeholder',
        'dates'       => 'Dates to be announced',
        'description' => 'Placeholder description for this showcase. Change this text in includes/data.php.',
        'comments'    => $defaultComments,
    ];
}

// ---------- Artspace events ----------
$events = [
    ['id' => 1, 'location' => 'New Zealand', 'title' => 'Endless Pastures', 'image' => 'images/events/event-1.png', 'date' => 'Date to be announced', 'time' => 'Time to be announced', 'venue' => 'Venue placeholder', 'description' => 'A sweeping landscape seemingly endless rows of beautifully drawn and painted art.'],
    ['id' => 2, 'location' => 'London', 'title' => 'St. Mary River', 'image' => 'images/events/event-2.png', 'date' => 'Date to be announced', 'time' => 'Time to be announced', 'venue' => 'Venue placeholder', 'description' => 'A grand alpine panorama showcasing majestic, jagged mountain ridges mirrored in a calm river.'],
    ['id' => 3, 'location' => 'City name', 'title' => 'Event Title 3', 'image' => 'images/events/event-3.png', 'date' => 'Date to be announced', 'time' => 'Time to be announced', 'venue' => 'Venue placeholder', 'description' => 'Placeholder description for the third event. Change this text in includes/data.php.'],
];
