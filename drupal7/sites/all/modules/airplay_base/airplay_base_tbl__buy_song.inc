<?php 
// $Id$



function ap_insertOrUpdate__buy_song ( &$aTitles )
{
	$tbl = ap_get_tbl__buy_song() ;	
	$his = ap_get_tbl__buy_song_history() ;	
	foreach ( $aTitles as &$aTitle ) 
	{
        $drt = $aTitle['data_record_type'] ;
        if ( $drt == 'record_store' )   continue;

		if ( $aTitle['data_record_type'] != 'song' ) continue;
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
// // 			dfb("Insert new buy_song_history");
			$his->insertNewTitle( $aTitle );
		}
	}
	$tbl = null;
	$his = null;
}


function ap_get_tbl__buy_song ( )
{
	$tbl = new AirplayTable( 'buy_song' );	
	$tbl->optionSet ( 'lookup_where', "WHERE song_id = :song_id AND record_store_id = :record_store_id AND media_format_id = :media_format_id" );
	$tbl->optionSet ( 'lookup_fields', array( 'song_id', 'record_store_id', 'media_format_id') );
	return $tbl;
}

function ap_get_tbl__buy_song_history ( )
{
	$tbl = new AirplayTable( 'buy_song_history' );	
	$tbl->optionSet ( 'lookup_where', "WHERE buy_song_id = :buy_song_id ORDER BY timestamp_updated DESC" );
	$tbl->optionSet ( 'lookup_fields', array( 'buy_song_id' ) );
	$tbl->optionSet ( 'assign_to_names', array('buy_song_id', 'price_local', 'currency_id', 'timestamp_updated') );
	return $tbl;
}
