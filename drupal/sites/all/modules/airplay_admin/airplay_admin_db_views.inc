<?php 
// $Id$

/*
SELECT DISTINCT (
song_simple_name
) 
FROM song 
    INNER JOIN song_simple ON song_simple.song_simple_id=song.song_simple_id 
    INNER JOIN buy_song ON song.song_id=buy_song.song_id 

*/


function ap_admin_view_buy_album_updated( $bDoCreateView = false ) 
{
	$sViewCreateSQL = "";
	if ( $bDoCreateView ) $sViewCreateSQL = "CREATE OR REPLACE VIEW view_buy_album AS ";
	return "$sViewCreateSQL
    SELECT buy_album_id, buy_album.record_store_id, record_store.record_store_name, 
    DATE_FORMAT( max( timestamp_updated ), '%Y/%m/%e - %H:%i') AS last_updated, 
    COUNT(buy_album_id) AS album_count 
    FROM buy_album
    INNER JOIN record_store ON buy_album.record_store_id = record_store.record_store_id
    GROUP BY record_store_id
    ORDER BY timestamp_updated
    ";
}


function ap_admin_view_buy_song_updated( $bDoCreateView = false ) 
{
    $sViewCreateSQL = "";
    if ( $bDoCreateView ) $sViewCreateSQL = "CREATE OR REPLACE VIEW view_buy_song AS ";
    return "$sViewCreateSQL
    SELECT buy_song_id, buy_song.record_store_id, record_store.record_store_name, 
    DATE_FORMAT( max( timestamp_updated ), '%Y/%m/%e - %H:%i') AS last_updated, 
    COUNT(buy_song_id)  AS song_count
    FROM buy_song
    INNER JOIN record_store ON buy_song.record_store_id = record_store.record_store_id
    GROUP BY record_store_id
    ORDER BY timestamp_updated
    ";
}


