<?php 
// $Id$



function ap_insertOrUpdate__buy_album ( &$aTitles )
{
	$tbl = ap_get_tbl__buy_album() ;	
	$his = ap_get_tbl__buy_album_history() ;	
	foreach ( $aTitles as &$aTitle ) 
	{
		if ( $aTitle['data_record_type'] != 'album' ) continue;
		$aTitle['price_local'] = (int)($aTitle['price_local'] *100);
		$tbl->lookupTitleId( $aTitle );
		$tbl->insertNewTitle( $aTitle );
		$tbl->updateTitleByIndex( $aTitle );
		
		
		$bInserNewHistoryRecord = false;
// // 		dfb ( $queryResult, "queryResult" );
		$queryResult = $his->lookupRaw($aTitle);
		$his->setCorrectDB();
		$a = db_fetch_array($queryResult);
// // 		dfb ( $a, "a" );
		if ( $a ) {
			$bInserNewHistoryRecord = true;
			if ( 	$a['price_local'] == $aTitle['price_local'] && 
					$a['currency_id'] == $aTitle['currency_id']) {
				$bInserNewHistoryRecord = false;
			}	
		}
		else $bInserNewHistoryRecord = true;
		$his->restoreDefaultDB();
		
		if ( $bInserNewHistoryRecord) 
		{
// // 			dfb("Insert new buy_album_history");
			$his->insertNewTitle( $aTitle );
		}
	}
	$tbl = null;
	$his = null;
}


function ap_get_tbl__buy_album ( )
{
	$tbl = new AirplayTable( 'buy_album' );	
	$tbl->optionSet ( 'lookup_where', "WHERE album_id = %d AND record_store_id = %d AND media_format_id= %d" );
	$tbl->optionSet ( 'lookup_fields', array( 'album_id', 'record_store_id', 'media_format_id') );
	return $tbl;
}

function ap_get_tbl__buy_album_history ( )
{
	$tbl = new AirplayTable( 'buy_album_history' );	
	$tbl->optionSet ( 'lookup_where', "WHERE buy_album_id = %d ORDER BY timestamp_updated DESC" );
	$tbl->optionSet ( 'lookup_fields', array( 'buy_album_id' ) );
	$tbl->optionSet ( 'assign_to_names', array('buy_album_id', 'price_local', 'currency_id', 'timestamp_updated') );
	return $tbl;
}
