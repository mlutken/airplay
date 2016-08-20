<?php
date_default_timezone_set('Europe/Copenhagen');
require_once ( __DIR__ . '/../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');
require_once ('utils/CmdLineArgs.php');
require_once ('db_manip/AllDbTables.php');

$g_useRedis = false;

// Create commandline parser giving default values for parameters.
$cmd = new CmdLineArgs("modulo-print=1000;start=-1;end=-1;verbose=NOT_SET");

// OPTIONAL: Create help text
$cmd->helpSet(array(
		'INTRO' 		=> "Remove price duplicates from DB. Enter start and end item_price_id.\nWill take a long time on our current production DB."
    ,   'modulo-print'  => "Print progress output for every N prices."
    ,   'start'         => "Start ID. If you enter -1 the first id of the table will be used as start."
    ,   'end'           => "End ID. If you enter -1 the last id of the table will be used as start."
    ,   'verbose'       => "Use verbose output."
));



$cmd->checkPrintHelp();

$dbAll = new AllDbTables();
$dbPrice = $dbAll->m_dbItemPriceData;

$start = $cmd->getValueInt("start");
$end   = $cmd->getValueInt("end");
if ($start == -1) $start = $dbPrice->getFirstID();
if ($end   == -1) $end   = $dbPrice->getLastID();

$modulo_print = $cmd->getValueInt("modulo-print");

printf ("Removing price duplicates from DB\n" );
printf ("modulo-print      : '%d'\n", $modulo_print );
printf ("start             : '%d'\n", $start );
printf ("end               : '%d'\n", $end );
printf ("Use verbose       : '%d'\n", $cmd->argumentIsSet("verbose") );
printf("\n");

$n = 0;
for ( $id = $start; $id <= $end; $i++, $id++) {
	$n++;
	
	// Lookup the base data for price ID and see if it is a valid one at all
	$aData = $dbPrice->getBaseData($id);
	if (count($aData) == 0) {
		$bDoPrint = $cmd->argumentIsSet("verbose") || ( $n % $modulo_print == 0 );
		if ($bDoPrint) printf("Skipping invalid ID: $id\n");
		continue;
	}
	
	// Now we know the id is valid we try looking it up from the base data.
	// If we get more than one result, which is indicated by a negative value we 
	// simply erase the price with the current id
	$lookup_id = $dbPrice->toID($aData);
	if ($lookup_id < -1) {
		$num_duplicates = -$lookup_id;
		printf("Found $num_duplicates duplicates removing the one with id $id named: '{$aData['item_price_name']}'\n");
		$dbPrice->erase($id);
	}
	if ( $n % $modulo_print == 0 ) {
		printf("Count: $n, id: $id '{$aData['item_price_name']}'\n");
	}
}

?>