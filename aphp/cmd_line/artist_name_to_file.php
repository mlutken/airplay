<?php
require_once ( __DIR__ . '/../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');

// // require_once ('utils/general_utils.php');
// // require_once ('utils/string_utils.php');
// // require_once ('db_api/db_string_utils.php');
require_once ('db_api/db_helpers.php');

printf("artist_name_to_file\n");
global $argv, $g_MySqlPDO;
    
if (isset($argv[1]) && isset($argv[2]) && $argv[1] != "" && $argv[2] != "") {
	$save_file_name = "artist_name.txt";
 	$q = "SELECT artist_name FROM artist ORDER BY artist_id ASC LIMIT $argv[1], $argv[2]";
   	$rows = pdoQueryAllRowsFirstElem($g_MySqlPDO, $q, array() );
	$s = '';
	foreach( $rows as $name)
	{
		$s .= $name . "\n";
	}
	file_put_contents( $save_file_name, $s);
	
	print ("\nFile saved to: $save_file_name\n\n");
} else {
	print ("\nCMD: artist_name_to_file.php START END (artist_name_to_file.php 0 1000 - gets first 1000 artists).\n\n");
}
?>