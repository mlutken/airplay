<?php
require_once ( __DIR__ . '/../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');

require_once ('db_api/ArtistDataMySql.php');
require_once ('db_api/ItemDataMySql.php');
require_once ('db_api/GenreLookup.php');
require_once ('db_api/MediaFormatLookup.php');
require_once ('db_api/MediaTypeLookup.php');


// $host       =    "localhost";
// $dbname     =    "airplay_music";
// $user       =    "airplay_user";
// $pass       =    "Deeyl1819";
// 
// $g_MySqlPDO = new PDO( "mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass );

// // $link = mysql_connect($host, $user, $pass );
// // if (!$link) {
// //     die('Could not connect: ' . mysql_error());
// // }
// // mysql_set_charset('utf8');
// // echo "Connected successfully\n";
// // mysql_select_db($dbname);
////if ( !mysql_select_db( DBNAME ) ) { logfile::I()->writeLog ( "Could not select database : " . mysql_error() , __file__ );return; }  



// -----------------------------
// --- Get DB info functions ---
// -----------------------------
$m1 = new ArtistDataMySql();

//print_r ($aRes);



    

$a = $m1->getBaseData(10);
//var_dump($a);
// $a = $m1->old_getSimpleAlbumIDs(3);
// var_dump($a);
// $a = $m1->old_getSimpleSongIDs(3);
// var_dump($a);
$a = $m1->getItemBaseIDs(5, 0);
//var_dump($a);


//$a = $m1->createNew("Hej Matematik");
//var_dump($a);


$aData = array( "artist_id" => 10, "artist_url" => "http://www.hejmatematik.dk", "country_id" => 45, "genre_id" => 1, "artist_type" => "G", "year_start" => "2005" );
//$aData = array( "artist_id" => 10, "genre_id" => 4, "year_start" => "2007" );

$a = $m1->updateBaseData($aData);
//var_dump($a);


// ----------------------------------
// --- Artist name and alias test ---
// ----------------------------------
// $m1 = new ArtistDataMySql($g_MySqlPDO);
// $artist_id = $m1->nameToID("Knut Aafløy");
// printf("artist_id: %d\n", $artist_id);
// $artist_id = $m1->aliasToID("Aafløy knut");
// printf("artist_id: %d\n", $artist_id);

//exit(0);

// -------------------------
// --- Item test (Album) ---
// -------------------------
$m1 = new ArtistDataMySql($g_MySqlPDO);
$artist_id = $m1->nameToID("Knut Aafløy");
printf("artist_id: %d\n", $artist_id);

$m2 = new ItemDataMySql($g_MySqlPDO);
$item_base_id = $m2->baseNameToID( $artist_id, 'The Sugar & The Salt', 0 );
printf("item_base_id: %d\n", $item_base_id);


$a = $m2->getBaseData( $item_base_id );
//var_dump($a);

//exit(0);

// $a = array();
// $a['item_base_id'] = $item_base_id;
// $a['record_label_id'] = 50;
// var_dump($a);

$a['child_items'] = '110,111,112';
$m2->updateBaseData($a);
//var_dump($a);

$a = $m2->getChildSongIDs( $item_base_id );
//var_dump($a);

//$a = $m2->createNew ($artist_id, "Min sang", 1);
//var_dump($a);


// -------------------------
// --- Test genre lookup ---
// -------------------------
// $g = new GenreLookup;
// $sGenreName= "Pop/rock";
// $genre_id = $g->nameToID($sGenreName);
// printf ("Converting: '$sGenreName' to: %d\n", $genre_id );
// 
// $genre_name = $g->IDToName($genre_id);
// printf ("Official AP genre name of $genre_id is: '%s' \n", $genre_name );
// 
// $sGenreName= "fance";
// $genre_id = $g->lookupID($sGenreName);
// printf ("Lookup: '$sGenreName' to: %d  , unknown: '%s'\n", $genre_id, $g->latestUnknownLookup() );
// $v = $g->latestUnknownSaveToDB(3, "http://mystore.dk/somepath1" );
// var_dump($v);
// 
// $unknownGenresDB = new UnknownGenresDataMySql($g_MySqlPDO);
// $a = $unknownGenresDB->getPageData();
// var_dump($a);

// --------------------------------
// --- Test media_format lookup ---
// --------------------------------
// $g = new MediaFormatLookup;
// $sName= "Vinyl";
// $id = $g->nameToID($sName);
// printf ("Converting: '$sName' to: %d\n", $id );
// 
// $name = $g->IDToName($id);
// printf ("Official AP name of $id is: '%s' \n", $name );
// 
// $sName= "ogg";
// $id = $g->lookupID($sName);
// printf ("Lookup: '$sName' to: %d  , unknown: '%s'\n", $id, $g->latestUnknownLookup() );
// $v = $g->latestUnknownSaveToDB(3, "http://mystore.dk/somepath1" );
// var_dump($v);
// 
// $unknownsDB = new UnknownGenresDataMySql($g_MySqlPDO);
// $a = $unknownsDB->getPageData();
// var_dump($a);



// --------------------------------
// --- Test media_format lookup ---
// --------------------------------
$g = new MediaTypeLookup;
$sName= "video";
$id = $g->nameToID($sName);
printf ("Converting: '$sName' to: %d\n", $id );

$name = $g->IDToName($id);
printf ("Official AP name of $id is: '%s' \n", $name );

$sName= "gert";
$id = $g->lookupID($sName);
printf ("Lookup: '$sName' to: %d  , unknown: '%s'\n", $id, $g->latestUnknownLookup() );
$v = $g->latestUnknownSaveToDB(3, "http://mystore.dk/somepath1" );
var_dump($v);

$unknownsDB = new UnknownGenresDataMySql($g_MySqlPDO);
$a = $unknownsDB->getPageData();
var_dump($a);

?>



