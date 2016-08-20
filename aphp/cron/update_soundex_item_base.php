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

$aItemBaseIds = getItemBaseIdsFromMySql( );
$countItemBase = count($aItemBaseIds);
print "Artist count: " . $countItemBase . "\n";



for ( $i = 0; $i < $countItemBase; $i++) {
	$item_base_id = $aItemBaseIds[$i]['item_base_id'];
	$item_base_name = $aItemBaseIds[$i]['item_base_name'];
	$soundex_string = getArtistSoundexString( $item_base_name );
	
	if ( $show_progress == true && ($i % 100 == 0) ) {
		printf("Progress[%d]: '%s'\n", $i, $item_base_name);
		//sleep(1);
	}
	updateSoundex($soundex_string, $item_base_id);
}


/*******************
    Functions 
*******************/

function getItemBaseIdsFromMySql( )
{
	global $g_MySqlPDO;
	$q = "SELECT item_base_id, item_base_name FROM item_base WHERE item_base_soundex = ''";
	$aArtistId = pdoQueryAssocRows($g_MySqlPDO, $q, array() );
	return $aArtistId;
}

function getArtistSoundexString( $item_base_name )
{
	$soundex_string = "";
	$artist_words = explode(" ", $item_base_name);
	$artist_word_count = count($artist_words);
	
	for ($i = 0; $i <= $artist_word_count; $i++) {
		$soundex_string .= " " . soundex($artist_words[$i]);
	}

	$soundex_string = trim($soundex_string);
	
	return $soundex_string;
}

function updateSoundex( $item_base_soundex , $item_base_id )
{
	global $g_MySqlPDO;
	$q = "UPDATE item_base SET item_base_soundex = '$item_base_soundex' WHERE item_base_id = $item_base_id";
    $stmt = $g_MySqlPDO->prepare($q, array());
    $stmt->execute(array($q));
}

?>