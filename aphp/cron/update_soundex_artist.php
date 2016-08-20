<?php
require_once ( __DIR__ . '/../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');

require_once ('utils/general_utils.php');
require_once ('utils/string_utils.php');
require_once ('db_api/db_string_utils.php');
require_once ('db_manip/AllDbTables.php');

$show_progress = false;

global $argv;

if (isset($argv[1]) && $argv[1] == "true") {
	$show_progress = true;
}

$dba = new AllDbTables();

$aArtistIds = getArtistIdsFromMySql( );
$countArtists = count($aArtistIds);
print "Artist count: " . $countArtists . "\n";



for ( $i = 0; $i < $countArtists; $i++) {
	$artist_id = $aArtistIds[$i]['artist_id'];
	$artist_name = $aArtistIds[$i]['artist_name'];
	$soundex_string = getArtistSoundexString( $artist_name );
	
	if ( $show_progress == true && ($i % 100 == 0) ) {
		printf("Progress[%d]: '%s'\n", $i, $artist_name);
		//sleep(1);
	}
	updateSoundex($soundex_string, $artist_id);
}


/*******************
    Functions 
*******************/

function getArtistIdsFromMySql( )
{
	global $g_MySqlPDO;
	$q = "SELECT artist_id, artist_name FROM artist WHERE artist_soundex = ''";
	$aArtistId = pdoQueryAssocRows($g_MySqlPDO, $q, array() );
	return $aArtistId;
}

function getArtistSoundexString( $artist_name )
{
	$soundex_string = "";
	$artist_words = explode(" ", $artist_name);
	$artist_word_count = count($artist_words);
	
	for ($i = 0; $i <= $artist_word_count; $i++) {
		$soundex_string .= " " . soundex($artist_words[$i]);
	}

	$soundex_string = trim($soundex_string);
	
	return $soundex_string;
}

function updateSoundex( $artist_soundex , $artist_id )
{
	global $g_MySqlPDO;
	$q = "UPDATE artist SET artist_soundex = '$artist_soundex' WHERE artist_id = $artist_id";
    $stmt = $g_MySqlPDO->prepare($q, array());
    $stmt->execute(array($q));
}

?>