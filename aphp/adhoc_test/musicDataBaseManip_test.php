<?php
require_once ( __DIR__ . '/../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');

$g_useRedis = false;

require_once ('db_manip/MusicDatabaseManip.php');

$id  = -1;
$id2 = -1;
global $argv, $argc;

if ( count($argv) > 1 ) {
    $id = $argv[1];
}

if ( count($argv) > 2 ) {
    $id2 = $argv[2];
}

printf ("MusicDatabaseManip: '$id', '$id2' \n" );
$m = new MusicDatabaseManip(null, null);
//$m->eraseArtist($id);
$m->mergeArtist ($id, $id2);

//$m->mergeItemBase ($id, $id2);

	
/*
---------------------------------------
--- Empty key tables for easy test ----
---------------------------------------
TRUNCATE `artist`;
TRUNCATE `artist_synonym`;
TRUNCATE `info_artist`;
TRUNCATE `item_base`;
TRUNCATE `item_price`;
TRUNCATE `record_store`;


*/

//var_dump($v);

// // $mf = new MediaFormatLookup;
// // $mf->dbg();
// // 
// // $mf = new MediaTypeLookup;
// // $mf->dbg();

?>