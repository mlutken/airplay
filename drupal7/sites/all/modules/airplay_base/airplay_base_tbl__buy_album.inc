<?php 
// $Id$



function ap_insertOrUpdate__buy_album ( &$aTitles )
{
	$tbl = ap_get_tbl__buy_album() ;	
	$his = ap_get_tbl__buy_album_history() ;	
	foreach ( $aTitles as &$aTitle ) 
	{
        $drt = $aTitle['data_record_type'] ;
        if ( $drt == 'record_store' )   continue;

        if ( $aTitle['data_record_type'] != 'album' ) continue;
		$aTitle['price_local'] = (int)($aTitle['price_local'] *100);
		$tbl->lookupTitleId( $aTitle );
		$tbl->insertNewTitle( $aTitle );
		$tbl->updateTitleByIndex( $aTitle );
		
		
		$bInserNewHistoryRecord = false;
// // 		dfb ( $queryResult, "queryResult" );
		$queryResult = $his->lookupRaw($aTitle);
		$his->setCorrectDB();
		$a = $queryResult->fetchAssoc();
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
	$tbl->optionSet ( 'lookup_where', "WHERE album_id = :album_id AND record_store_id = :record_store_id AND media_format_id = :media_format_id" );
	$tbl->optionSet ( 'lookup_fields', array( 'album_id', 'record_store_id', 'media_format_id') );
	return $tbl;
}

function ap_get_tbl__buy_album_history ( )
{
	$tbl = new AirplayTable( 'buy_album_history' );	
	$tbl->optionSet ( 'lookup_where', "WHERE buy_album_id = :buy_album_id ORDER BY timestamp_updated DESC" );
	$tbl->optionSet ( 'lookup_fields', array( 'buy_album_id' ) );
	$tbl->optionSet ( 'assign_to_names', array('buy_album_id', 'price_local', 'currency_id', 'timestamp_updated') );
	return $tbl;
}
