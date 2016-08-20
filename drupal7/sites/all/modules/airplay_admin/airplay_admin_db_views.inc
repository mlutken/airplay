<?php 
// $Id$
/*
    TODO: remove count(*) from INNODB
*/

function ap_admin_view_buy_album_updated( $bDoCreateView = false ) 
{
	$sViewCreateSQL = "";
    if ( $bDoCreateView ) $sViewCreateSQL = "CREATE OR REPLACE VIEW view_buy_album AS ";
	return "$sViewCreateSQL
    SELECT record_store.record_store_name, 
    DATE_FORMAT( max( timestamp_updated ), '%Y/%m/%d - %H:%i') AS last_updated, 
    COUNT(item_price.record_store_id) AS album_count 
    FROM item_price
    INNER JOIN record_store ON item_price.record_store_id = record_store.record_store_id
    WHERE item_type = 1
    GROUP BY record_store.record_store_name
    ORDER BY timestamp_updated
    ";
}


function ap_admin_view_buy_song_updated( $bDoCreateView = false ) 
{
    $sViewCreateSQL = "";
    if ( $bDoCreateView ) $sViewCreateSQL = "CREATE OR REPLACE VIEW view_buy_album AS ";
	return "$sViewCreateSQL
    SELECT record_store.record_store_name, 
    DATE_FORMAT( max( timestamp_updated ), '%Y/%m/%d - %H:%i') AS last_updated, 
    COUNT(item_price.record_store_id) AS song_count 
    FROM item_price
    INNER JOIN record_store ON item_price.record_store_id = record_store.record_store_id
    WHERE item_type = 2
    GROUP BY record_store.record_store_name
    ORDER BY timestamp_updated
    ";
}

function ap_admin_view_album_song_merchandise_updated( ) 
{
	return "SELECT item_type, DATE_FORMAT( last_updated , '%Y/%m/%d - %H:%i') AS last_updated, record_store.record_store_name, item_count
    FROM (
    SELECT
        item_price.record_store_id, item_type, MAX( timestamp_updated ) AS last_updated, COUNT(item_price.record_store_id) AS item_count 
        FROM item_price
        GROUP BY item_price.record_store_id, item_type
    ) AS Res
    INNER JOIN record_store ON Res.record_store_id = record_store.record_store_id 
    ORDER BY record_store_name
    ";
}


function ap_admin_items_count( ) 
{
	return "SELECT COUNT(*) AS item_count , item_type FROM item_base GROUP BY item_type ORDER BY item_type";
}