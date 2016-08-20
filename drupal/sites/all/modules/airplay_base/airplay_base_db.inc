<?php 
// $Id$


/** Insert or update price data in the DB */
function airplay_insert_update_data( &$aTitles ) 
{
	
	$iCount = count ($aTitles);
														ap_ins_upd_print ( "insert_update $iCount titles" );
														ap_ins_upd_print ( "airplay_assign_simple_ids ..." );
	airplay_assign_simple_ids			( $aTitles );
														ap_ins_upd_print ( "Table subgenre ..." );
	ap_insertOrUpdate__subgenre			( $aTitles );
														ap_ins_upd_print ( "Table record_label ..." );
	ap_insertOrUpdate__record_label		( $aTitles );
														ap_ins_upd_print ( "Table record_store ..." );
	ap_insertOrUpdate__record_store 	( $aTitles );
														ap_ins_upd_print ( "Table artist ..." );
	ap_insertOrUpdate__artist 			( $aTitles );
														ap_ins_upd_print ( "Table album_simple ..." );
	ap_insertOrUpdate__album_simple		( $aTitles );
														ap_ins_upd_print ( "Table album ..." );
	ap_insertOrUpdate__album 			( $aTitles );
                                                        ap_ins_upd_print ( "Table song_simple ..." );
    ap_insertOrUpdate__song_simple      ( $aTitles );
														ap_ins_upd_print ( "Table song ..." );
	ap_insertOrUpdate__song 			( $aTitles );
 														ap_ins_upd_print ( "Table album_song_rel ..." );
 	ap_insertOrUpdate__album_song_rel	( $aTitles );
														ap_ins_upd_print ( "Table buy_album ..." );
	ap_insertOrUpdate__buy_album		( $aTitles ); 	
														ap_ins_upd_print ( "Table buy_song ..." );
	ap_insertOrUpdate__buy_song			( $aTitles ); 	

	$aTitles  = null;
}


/** Insert or update price data in the DB */
function airplay_insert_update_artist_info( &$aInfos ) 
{
	
	$iCount = count ($aInfos);
														ap_ins_upd_print ( "insert_update $iCount artist_info(s)" );
														ap_ins_upd_print ( "airplay_assign_simple_ids ..." );
	airplay_assign_simple_ids			( $aInfos );
	//var_dump ($aInfos);

														ap_ins_upd_print ( "Table subgenre ..." );
	ap_insertOrUpdate__subgenre			( $aInfos );
 														ap_ins_upd_print ( "Table artist ..." );
 	ap_insertOrUpdate__artist 			( $aInfos );
 														ap_ins_upd_print ( "Table info_artist ..." );
 	ap_insertOrUpdate__info_artist		( $aInfos );
// 														ap_ins_upd_print ( "Table album ..." );
// 	ap_insertOrUpdate__album 			( $aInfos );
//                                                         ap_ins_upd_print ( "Table song_simple ..." );
//     ap_insertOrUpdate__song_simple      ( $aInfos );
// 														ap_ins_upd_print ( "Table song ..." );
// 	ap_insertOrUpdate__song 			( $aInfos );
//  														ap_ins_upd_print ( "Table album_song_rel ..." );
//  	ap_insertOrUpdate__album_song_rel	( $aInfos );
// 														ap_ins_upd_print ( "Table buy_album ..." );
// 	ap_insertOrUpdate__buy_album		( $aInfos ); 	
// 														ap_ins_upd_print ( "Table buy_song ..." );
// 	ap_insertOrUpdate__buy_song			( $aInfos ); 	

	$aInfos = null;
}


function ap_ins_upd_print( $sText )
{
//  	drush_print( $sText );
 	print "$sText\n" ;
// 	print "SSSSSSSSSSSSS" ;
}