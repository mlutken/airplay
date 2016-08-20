<?php
require_once ( __DIR__ . '/../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');

global $g_idToGenre;
$g_idToGenre = array("Unknown", "Pop/Rock", "Soul/R&B");

$r = new Redis();

$r->connect("127.0.0.1");

// --- Artist: Kim Larsen ---
$r->set("artist:id:1000:name", "Kim Larsen");
$r->set("artist:id:1000:genre_id", "1");
$r->set("artist:id:1000:year_born", "1945");
$r->set("artist:kim_larsen:id", "1000");

$r->sadd("all_artists", "1000" );   // Set-add kim Larsens ID til globalt set med alle kunstnere 

// Album: Midt om natten
$r->set("artist:id:1000:album_id:1020:name", "Midt om natten");
$r->set("artist:id:1000:album_id:1020:year", "1984");
$r->sadd("artist:id:1000:all_albums", "1020");
$r->set("album:midt_om_natten:album_id", "1020");
$r->set("album:midt_om_natten:artist_id", "1000");

// Album prices: Midt om natten
$r->set("artist:id:1000:album_id:1020:price_id:1030:price", "59");

// Album: Forklædt som voksen
$r->set("artist:id:1000:album_id:1021:name", "Forklædt som voksen");
$r->set("artist:id:1000:album_id:1021:year", "1985");
$r->sadd("artist:id:1000:all_albums", "1021");

// Song: Forklædt som voksen
$r->set("artist:id:1000:song_id:1022:name", "Forklædt som voksen");
$r->set("artist:id:1000:song_id:1022:time", "204");
$r->sadd("artist:id:1000:all_songs", "1022");
$r->sadd("artist:id:1000:album_id:1021:all_songs", "1022");

// Song: Fru Sauterne
$r->set("artist:id:1000:song_id:1023:name", "Fru Sauterne");
$r->set("artist:id:1000:song_id:1023:time", "183");
$r->sadd("artist:id:1000:all_songs", "1023");
$r->sadd("artist:id:1000:album_id:1021:all_songs", "1023");




$id = $r->get("artist:kim_larsen:id");
printf( "--------------------------------------------\n" );
printf( "%s  (%s)\n", $r->get("artist:id:$id:name"), $g_idToGenre[ $r->get("artist:id:$id:genre_id") ] );
printf( "--------------------------------------------\n" );
printf( "Født: %d\n", $r->get("artist:id:$id:year_born") );


$aAlbumIds = $r->sMembers("artist:id:$id:all_albums");
printf( "Album list:\n" );
foreach( $aAlbumIds as $album_id ) {
    printf ("%s,   %s\n", $r->get("artist:id:$id:album_id:$album_id:name"), $r->get("artist:id:$id:album_id:$album_id:year" ) );
}






// // // --- Album: Midt om natten ---
// // $r->set ("album:id:1020:name", "Midt om natten");
// // $r->sadd("album:id:1020:artist_id", "1000");
// // $r->set ("album:midt_om_natten:id", "1020");

?>
