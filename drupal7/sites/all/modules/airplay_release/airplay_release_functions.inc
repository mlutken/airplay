<?php 

function getReleaseListFrontpage()
{
    /* Release list for albums item_type = 1 */
    $sQuery = "SELECT artist_name, item_base_name, item_base.release_date
    FROM artist
    INNER JOIN item_base ON item_base.artist_id = artist.artist_id
    WHERE item_base.item_type = 1 AND item_base.release_date < now()
    ORDER BY item_base.release_date desc
    ";

    db_set_active('airplay_music_v1');
    
    $query_result =  db_query_range($sQuery, 0, 5, array());

    $aAllRows = array();
    while ( $a = $query_result->fetchAssoc() ) {
        $aAllRows[] = $a;
    }
    db_set_active('default');
    return $aAllRows;
}

function getPreReleaseListFrontpage()
{
    /* Release list for albums item_type = 1 */
    $sQuery = "SELECT artist_name, item_base_name, item_base.release_date
    FROM artist
    INNER JOIN item_base ON item_base.artist_id = artist.artist_id
    WHERE item_base.item_type = 1 AND item_base.release_date > DATE_ADD(NOW(), INTERVAL 1 DAY)
    ORDER BY item_base.release_date asc
    ";

    db_set_active('airplay_music_v1');
    
    $query_result =  db_query_range($sQuery, 0, 5, array());

    $aAllRows = array();
    while ( $a = $query_result->fetchAssoc() ) {
        $aAllRows[] = $a;
    }
    db_set_active('default');
    return $aAllRows;
}