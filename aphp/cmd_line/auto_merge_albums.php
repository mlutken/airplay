<?php
//date_default_timezone_set('Europe/Copenhagen');
require_once ( __DIR__ . '/../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');
require_once ('db_manip/AutoMergerAlbums.php');


//printf ("auto_merge_albums\n" );
$ama = new AutoMergerAlbums();

//$ama->mergeArtistById(870);

$ama->mergeArtistsWithItemMasters(0,0);

?>