<?php
require_once ( __DIR__ . '/../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');
require_once ('db_api/ArtistDataMySql.php');
require_once ('utils/string_utils.php');

/**
-----------------------------------------
--- artist_automerge_data_to_json.php ---
-----------------------------------------
Simple dev/test program to write test data for auto merge develop into a json file.


*/

global $argv, $g_MySqlPDO;

$dataDir = "test_data/auto_merge";

$artist_name = $argv[1];
$item_type = 1;

printf("artist_automerge_data_to_file: '{$artist_name}'\n");
$ad = new ArtistDataMySql;
$artist_id = $ad->lookupID( $artist_name );
if ( 0 == $artist_id ) {
	printf("Error: Could not find artistname: '{$artist_name}'\n");
	exit(0);
}

// -------------
// --- Setup ---
// -------------
@mkdir( $dataDir, 0755, true ); // The @ suppresses php warnings. Here we particularly want to disregard the dir exists warning when creating



// -------------
// --- Do it ---
// -------------
printf("Artist ID is: {$artist_id}\n");

$aData = $ad->getAutoMergeData($artist_id, $item_type);

$s = json_encode( $aData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );


$fileName = nameToUrl($artist_name) . "__$artist_id.json";
$filePath = $dataDir . "/" . $fileName;

writeFileDbFile( $filePath, $aData );
print "$filePath\n";
//echo $s;

?>