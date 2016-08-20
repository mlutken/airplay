<?php
require_once ( __DIR__ . '/../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');

require_once ('utils/general_utils.php');
require_once ('utils/string_utils.php');
require_once ('db_api/db_string_utils.php');
require_once ('db_manip/AllDbTables.php');


global $argv;

    if (count($argv) >= 2) {
        $show_progress = false;
        $start 		= 0;
        $count 		= 0;

        if (isset($argv[1]) && $argv[1] == "true") {
            $show_progress = true;
        }
        if (isset($argv[2])) {
            $start 		= $argv[2];
        }
        if (isset($argv[3])) {
            $count 		= $argv[3];
        }

        if ($start == 0 && $count == 0) {
            print "Full run...\n";
        } else {
            print "Run from $start to $count.\n";
        }

        $dba = new AllDbTables();

        $aArtistIds = getArtistIdsFromMySql( $start, $count );
        $countArtists = count($aArtistIds);
        print "Artist count: " . $countArtists . "\n";



        for ( $i = 0; $i < $countArtists; $i++) {
            $artist_id = $aArtistIds[$i]['artist_id'];
            $aArtistCount = getArtistCountFromMySql( $artist_id );
            if ( $show_progress == true && ($i % 100 == 0) ) {
                printf("Progress[%d]: '%s'\n", $i, $aArtistIds[$i]['artist_name']);
                //sleep(1);
            }
            updateArtistItemCount($aArtistCount["total_count"], $aArtistCount["artist_id"]);
        }
    } else {
        print "File parameters:\n";
        print "1st parameter - show progress true/false.\n";
        print "2nd parameter - start artist.\n";
        print "3rd parameter - end artist.\n";
        print "update_item_prices_count_artist.php true - full run with progress.\n";
    }


/*******************
    Functions 
*******************/

function getArtistIdsFromMySql( $start, $count )
{
	global $g_MySqlPDO;
    // Full run
    if ($start == 0 && $count == 0) {
        $q = "SELECT artist_id, artist_name FROM artist ORDER BY artist_id ASC";
    } else {
        $q = "SELECT artist_id, artist_name FROM artist ORDER BY artist_id ASC LIMIT $start, $count";
    }
	$aArtistId = pdoQueryAssocRows($g_MySqlPDO, $q, array() );
	return $aArtistId;
}

function getArtistCountFromMySql( $artist_id )
{
	global $g_MySqlPDO;
	$q = "SELECT COUNT(*) total_count, artist_id FROM item_price WHERE artist_id = $artist_id";
	$a = pdoQueryAssocFirstRow($g_MySqlPDO, $q, array() );
	return $a;
}

function updateArtistItemCount( $count , $artist_id )
{
	global $g_MySqlPDO;
	$q = "UPDATE artist SET item_price_count = $count WHERE artist_id = $artist_id";
    $stmt = $g_MySqlPDO->prepare($q, array());
    $stmt->execute(array($q));

}



?>