<?php
	require_once ( __DIR__ . '/../aphp_fix_include_path.php' );
	require_once ('airplay_globals.php');

	require_once ('utils/general_utils.php');
	require_once ('db_api/db_string_utils.php');
	require_once ('db_manip/AllDbTables.php');


	print "Delete merchandise prices older then 10 days - for shops running weekly.\n";
	deleteWeeklyShops();
	
	print "Delete merchandise prices older then 35 days - for shops running monthly.\n";
	deleteMonthlyShops();


	/*******************
		Functions 
	*******************/

	function deleteWeeklyShops( )
	{
		global $g_MySqlPDO;
		$q = "DELETE FROM item_price WHERE timestamp_updated <= DATE_ADD(now(), INTERVAL -10 DAY) AND item_type = 3 AND record_store_id NOT IN (28, 55)";
		$stmt = $g_MySqlPDO->prepare($q, array());
		$stmt->execute(array($q));
	}
	
	function deleteMonthlyShops( )
	{
		global $g_MySqlPDO;
		$q = "DELETE FROM item_price WHERE timestamp_updated <= DATE_ADD(now(), INTERVAL -40 DAY) AND item_type = 3 AND record_store_id IN (28, 55)";
		$stmt = $g_MySqlPDO->prepare($q, array());
		$stmt->execute(array($q));
	}

?>