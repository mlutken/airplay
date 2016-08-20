<?php 

function airplay_view_buy_song( $bDoCreateView = false ) 
{
	$sViewCreateSQL = "";
	if ( $bDoCreateView ) $sViewCreateSQL = "CREATE OR REPLACE VIEW view_buy_song AS ";
	return "$sViewCreateSQL
    SELECT artist.artist_name, song.song_name, song.song_id, song_simple_name, 
    (price_local * currency_to_euro.to_euro * currency.from_euro * 0.01) as price,
    record_store.record_store_name, currency.currency_name, 
    media_format.media_format_name, buy_song.buy_at_url, record_store.use_affiliate, record_store.affiliate_link, record_store.affiliate_encode_times
    FROM artist
    INNER JOIN song ON song.artist_id=artist.artist_id 
    INNER JOIN buy_song ON song.song_id=buy_song.song_id 
    INNER JOIN song_simple ON song_simple.song_simple_id=song.song_simple_id 
    INNER JOIN record_store ON buy_song.record_store_id=record_store.record_store_id 
    INNER JOIN media_format ON media_format.media_format_id=buy_song.media_format_id
    INNER JOIN currency_to_euro ON currency_to_euro.currency_id=buy_song.currency_id   
    INNER JOIN currency ON currency.currency_name = :currency_name   
";
}

// --------------------
// --- Db functions ---
// --------------------

function lookupExact_albums($album_simple_name)
{
    if ($album_simple_name == "" ) return array();
	$sQuery = "
    SELECT item_base_name, artist_name   
    FROM artist 
    INNER JOIN item_base ON item_base.artist_id = artist.artist_id 
    WHERE item_base.item_base_name = :item_base_name AND item_base.item_type = 1
    ORDER BY artist_name ASC
    ";
    $aArgs = array ( ':item_base_name' => $album_simple_name);
    db_set_active('airplay_music_v1');
    $query_result = db_query_range($sQuery, 0, 100, $aArgs);
    
    $aAllRows = array();
    while ( $a = $query_result->fetchAssoc() ) {
        $aAllRows[] = $a;
    } 
    db_set_active('default');

    return $aAllRows;   
}


function lookupExact_songs($song_simple_name)
{
    if ($song_simple_name == "" ) return array();
	$sQuery = "
    SELECT item_base_name, artist_name   
    FROM artist 
    INNER JOIN item_base ON item_base.artist_id = artist.artist_id 
    WHERE item_base.item_base_name = :item_base_name AND item_base.item_type = 2
    ORDER BY artist_name ASC
    ";

    $aArgs = array ( ':item_base_name' => $song_simple_name);
    db_set_active('airplay_music_v1');
    $query_result = db_query_range($sQuery, 0, 100, $aArgs);
    
    $aAllRows = array();
    while ( $a = $query_result->fetchAssoc() ) {
        $aAllRows[] = $a;
    } 
    db_set_active('default');

    return $aAllRows;   
}


function lookupSimilar_albums($album_simple_name)
{
    if ($album_simple_name == "" ) return array();
	$sQuery = "
    SELECT item_base_name, artist_name   
    FROM artist 
    INNER JOIN item_base ON item_base.artist_id = artist.artist_id 
    WHERE item_base.item_base_name LIKE :item_base_name AND item_base.item_type = 1
    ORDER BY artist_name ASC
    ";
	$aArgs = array ( ':item_base_name' => $album_simple_name."%");
    db_set_active('airplay_music_v1');
    $query_result = db_query_range($sQuery, 0, 300, $aArgs );
    
    $aAllRows = array();
    while ( $a = $query_result->fetchAssoc() ) {
        $aAllRows[] = $a;
    } 
    db_set_active('default');

    return $aAllRows;   
}

function lookupSimilar_songs($song_simple_name)
{
    if ($song_simple_name == "" ) return array();
	$sQuery = "
    SELECT item_base_name, artist_name   
    FROM artist 
    INNER JOIN item_base ON item_base.artist_id = artist.artist_id 
    WHERE item_base.item_base_name LIKE :item_base_name AND item_base.item_type = 2
    ORDER BY artist_name ASC
    ";
	$aArgs = array ( ':item_base_name' => $song_simple_name."%");
    db_set_active('airplay_music_v1');
    $query_result = db_query_range($sQuery, 0, 300, $aArgs );
    
    $aAllRows = array();
    while ( $a = $query_result->fetchAssoc() ) {
        $aAllRows[] = $a;
    } 
    db_set_active('default');

    return $aAllRows;   
}

function getArtist_media_format_count($artist_id, $item_type) {
	$item_count_name = "";

	if ($item_type == 1) {
		$item_count_name = "album_prices_count";
	} else if ($item_type == 2) {
		$item_count_name = "song_prices_count";
	} else if ($item_type == 3) {
		$item_count_name = "merchandise_prices_count";
	} else if ($item_type == 4) {
		$item_count_name = "concert_prices_count";
	}
    $sQuery = "SELECT COUNT(*) as $item_count_name, media_format.media_format_name
        FROM artist
        INNER JOIN item_base ON item_base.artist_id = artist.artist_id 
        INNER JOIN item_price ON item_base.item_base_id = item_price.item_base_id 
        INNER JOIN media_format ON media_format.media_format_id = item_price.media_format_id
        WHERE artist.artist_id = :artist_id AND item_base.item_type = :item_type
        GROUP BY media_format_name
        ";
    $aArgs = array();
    $aArgs[':artist_id'] = $artist_id;
    $aArgs[':item_type'] = $item_type;
    db_set_active('airplay_music_v1');
    $query_result = db_query($sQuery, $aArgs);
    $aAllRows = array();
    while ( $a = $query_result->fetchAssoc() ) {
        $aAllRows[] = $a;
    } 
    db_set_active('default');
    return $aAllRows;
}

function getArtist_albums($artist_id, $currency, $media_format_name)
{
	$sQuery = "
    SELECT artist.artist_id, artist.artist_name, item_base.item_base_name, item_base.item_year,
    currency_to_euro.to_euro, COUNT(*) as album_prices_count, item_master,
    MIN(price_local * currency_to_euro.to_euro * currency.from_euro) as price_MIN,   
    MAX(price_local * currency_to_euro.to_euro * currency.from_euro) as price_MAX,
    media_format.media_format_name, image_url, image_width, image_height, item_base.item_base_id
    FROM artist
    LEFT JOIN item_base ON item_base.artist_id = artist.artist_id 
    LEFT JOIN item_price ON item_base.item_base_id = item_price.item_base_id 
    LEFT JOIN media_format ON media_format.media_format_id = item_price.media_format_id
    LEFT JOIN currency_to_euro ON currency_to_euro.currency_id = item_price.currency_id   
    LEFT JOIN currency ON currency.currency_name = :currency_name 
    ";
    $aArgs = array();
    $aArgs[':currency_name'] = $currency;
    $aArgs[':artist_id'] = $artist_id;
    $sQuery .= " WHERE artist.artist_id = :artist_id AND item_base.item_type = 1";
    if ( $media_format_name != "" && $media_format_name != 'ALL' ) {
        $aArgs[':media_format_name'] = $media_format_name;
        $sQuery .= " AND media_format_name = :media_format_name"; 
    }
    
    $sQuery .= " GROUP BY item_base_name ORDER BY item_base_name ASC , item_year ASC";

    db_set_active('airplay_music_v1');

    $query_result = db_query($sQuery, $aArgs);
    
    $aAllRows = array();
    while ( $a = $query_result->fetchAssoc() ) {
        $aAllRows[] = $a;
    } 
    db_set_active('default');

    return $aAllRows;
}

/**
\see LEFT JOIN and limiting the JOIN'd rows displayed
\see http://forums.mysql.com/read.php?52,211954,211954#msg-211954
\see http://forums.mysql.com/read.php?97,206159,206194#msg-206194 */
function getArtist_songs($artist_id, $currency, $media_format_name)
{
    /* Changed 5x INNER JOIN to LEFT JOIN */
	$sQuery = "SELECT artist.artist_id, artist.artist_name, item_base.item_year, item_base_name,
    currency_to_euro.to_euro, COUNT(*) as song_prices_count, 
    MIN(price_local * currency_to_euro.to_euro * currency.from_euro) as price_MIN,   
    MAX(price_local * currency_to_euro.to_euro * currency.from_euro) as price_MAX,
    media_format.media_format_name
    FROM artist
    LEFT JOIN item_base ON item_base.artist_id = artist.artist_id 
    LEFT JOIN item_price ON item_base.item_base_id = item_price.item_base_id 
    LEFT JOIN media_format ON media_format.media_format_id = item_price.media_format_id
    LEFT JOIN currency_to_euro ON currency_to_euro.currency_id = item_price.currency_id   
    LEFT JOIN currency ON currency.currency_name = :currency_name
	";

    $aArgs = array();
    $aArgs[':currency_name'] = $currency;
    $aArgs[':artist_id'] = $artist_id;
    $sQuery .= " WHERE artist.artist_id = :artist_id AND item_base.item_type = 2";
    if ( $media_format_name != "" && $media_format_name != 'ALL' ) {
        $aArgs[':media_format_name'] = $media_format_name;
        $sQuery .= " AND media_format_name = :media_format_name"; 
    }
    
    $sQuery .= " GROUP BY item_base_name ORDER BY item_base_name ASC , item_year ASC";

    db_set_active('airplay_music_v1');

    $query_result = db_query($sQuery, $aArgs);
    
    $aAllRows = array();
    while ( $a = $query_result->fetchAssoc() ) {
        $aAllRows[] = $a;
    } 
    db_set_active('default');

    return $aAllRows;
}

function getArtist_merchandise($artist_id, $currency, $media_format_name)
{
	$sQuery = "SELECT artist.artist_id, artist.artist_name, item_base.item_year, item_price_name,
    currency_to_euro.to_euro, COUNT(*) as merchandise_prices_count, cover_image_url, item_price_id,
    MIN(price_local * currency_to_euro.to_euro * currency.from_euro) as price_MIN,   
    media_format.media_format_name, item_price.buy_at_url, record_store.use_affiliate,
    record_store.affiliate_link, record_store.affiliate_encode_times, record_store.record_store_name
    FROM artist
    LEFT JOIN item_base ON item_base.artist_id = artist.artist_id 
    LEFT JOIN item_price ON item_base.item_base_id = item_price.item_base_id 
    LEFT JOIN media_format ON media_format.media_format_id = item_price.media_format_id
    INNER JOIN currency_to_euro ON currency_to_euro.currency_id = item_price.currency_id
    INNER JOIN record_store ON item_price.record_store_id = record_store.record_store_id
    INNER JOIN currency ON currency.currency_name = :currency_name
	";

    $aArgs = array();
    $aArgs[':currency_name'] = $currency;
    $aArgs[':artist_id'] = $artist_id;
    $sQuery .= " WHERE artist.artist_id = :artist_id AND item_base.item_type = 3";
    if ( $media_format_name != "" && $media_format_name != 'ALL' ) {
        $aArgs[':media_format_name'] = $media_format_name;
        $sQuery .= " AND media_format_name = :media_format_name"; 
    }
    
    $sQuery .= " GROUP BY item_price_name ORDER BY item_price_name ASC";

    db_set_active('airplay_music_v1');
    $query_result = db_query($sQuery, $aArgs);
    
    $aAllRows = array();
    while ( $a = $query_result->fetchAssoc() ) {
        $aAllRows[] = $a;
    } 
    db_set_active('default');

    return $aAllRows;
}


function getArtist_concerts($artist_id, $currency, $media_format_name)
{
   $sQuery = "SELECT artist.artist_id, artist.artist_name, item_event_date, item_event_time, item_price_name,
    currency_to_euro.to_euro, MIN(price_local * currency_to_euro.to_euro * currency.from_euro) as price_MIN,   
    media_format.media_format_name, item_price.buy_at_url, record_store.use_affiliate, record_store_event_date_text,
    record_store.affiliate_link, record_store.affiliate_encode_times, record_store.record_store_name, price_local, item_price_delivery_status_id, item_price.media_format_id
    FROM artist
    INNER JOIN item_base ON item_base.artist_id = artist.artist_id 
    INNER JOIN item_price ON item_base.item_base_id = item_price.item_base_id 
    INNER JOIN media_format ON media_format.media_format_id = item_price.media_format_id
    INNER JOIN currency_to_euro ON currency_to_euro.currency_id = item_price.currency_id
    INNER JOIN record_store ON item_price.record_store_id = record_store.record_store_id
    INNER JOIN currency ON currency.currency_name = :currency_name
	";

    $aArgs = array();
    $aArgs[':currency_name'] = $currency;
    $aArgs[':artist_id'] = $artist_id;
    $sQuery .= " WHERE ((item_event_date >= CURDATE()) OR (item_event_date = '0000-00-00' AND timestamp_updated >= DATE_ADD(now(), INTERVAL -10 DAY))) AND artist.artist_id = :artist_id AND item_base.item_type = 4";
    if ( $media_format_name != "" && $media_format_name != 'ALL' ) {
        $aArgs[':media_format_name'] = $media_format_name;
        $sQuery .= " AND media_format_name = :media_format_name"; 
    }
    
    $sQuery .= " GROUP BY item_price_name, item_event_date ORDER BY item_event_date ASC, item_event_time ASC";

    db_set_active('airplay_music_v1');
    $query_result = db_query($sQuery, $aArgs);
    
    $aAllRows = array();
    while ( $a = $query_result->fetchAssoc() ) {
        $aAllRows[] = $a;
    } 
    db_set_active('default');

    return $aAllRows;
}


function getBuyItems_albums($artist_id, $album_simple_id, $currency, $media_format_name)
{
	$sQuery = "SELECT record_store.record_store_id, item_base.item_base_id, artist.artist_name, item_base_name, item_price_name,
    (price_local * currency_to_euro.to_euro * currency.from_euro) as price_local,
    record_store.record_store_name, currency.currency_name, freight_price_da, freight_price_en,
    media_format.media_format_name, item_price.buy_at_url, record_store.use_affiliate,
    record_store.affiliate_link, record_store.affiliate_encode_times, item_used, item_grading, item_grading_cover, item_base.release_date
    FROM artist
    INNER JOIN item_base ON item_base.artist_id = artist.artist_id 
	INNER JOIN item_price ON item_base.item_base_id = item_price.item_base_id
	INNER JOIN record_store ON item_price.record_store_id = record_store.record_store_id
    INNER JOIN media_format ON media_format.media_format_id = item_price.media_format_id
    INNER JOIN currency_to_euro ON currency_to_euro.currency_id = item_price.currency_id   
    INNER JOIN currency ON currency.currency_name = :currency_name
	";

    db_set_active('airplay_music_v1');

    $aArgs = array();
    $aArgs[':currency_name'] = $currency;
    if ( $album_simple_id ) {
        $aArgs[':item_base_id'] = $album_simple_id;
        $aArgs[':artist_id'] = $artist_id;
        $sQuery .= " WHERE item_base.item_base_id = :item_base_id AND artist.artist_id = :artist_id AND item_base.item_type = 1";
    }
    /*if ( $media_format_name != "" && $media_format_name != 'ALL' ) {
        $aArgs[':media_format_name'] = $media_format_name;
        $sQuery .= " AND media_format_name = :media_format_name"; 
    }*/
    
    $sQuery .= " ORDER BY item_base_name ASC, price_local ASC";
    $query_result = db_query_range($sQuery, 0, 300, $aArgs);
    

    $aAllRows = array();
    while ( $a = $query_result->fetchAssoc() ) {
        $aAllRows[] = $a;
    } 
    db_set_active('default');
    return $aAllRows;
}



function getBuyItems_songs($artist_id, $song_simple_id, $currency, $media_format_name)
{
	$sQuery = "SELECT record_store.record_store_id, item_base.item_base_id, artist.artist_name, item_price_name, 
    (price_local * currency_to_euro.to_euro * currency.from_euro) as price_local,
    record_store.record_store_name, currency.currency_name, freight_price_da, freight_price_en,
    media_format.media_format_name, item_price.buy_at_url, record_store.use_affiliate,
    record_store.affiliate_link, record_store.affiliate_encode_times, item_used, item_grading, item_grading_cover
    FROM artist
    INNER JOIN item_base ON item_base.artist_id = artist.artist_id 
	INNER JOIN item_price ON item_base.item_base_id = item_price.item_base_id
	INNER JOIN record_store ON item_price.record_store_id = record_store.record_store_id
    INNER JOIN media_format ON media_format.media_format_id = item_price.media_format_id
    INNER JOIN currency_to_euro ON currency_to_euro.currency_id = item_price.currency_id   
    INNER JOIN currency ON currency.currency_name = :currency_name
	";
	
    db_set_active('airplay_music_v1');

    $aArgs = array();
    $aArgs[':currency_name'] = $currency;
    if ( $song_simple_id ) {
        $aArgs[':item_base_id'] = $song_simple_id;
        $aArgs[':artist_id'] = $artist_id;
        $sQuery .= " WHERE item_base.item_base_id = :item_base_id AND artist.artist_id = :artist_id AND item_base.item_type = 2";
    }
    /*if ( $media_format_name != "" && $media_format_name != 'ALL' ) {
        $aArgs[':media_format_name'] = $media_format_name;
        $sQuery .= " AND media_format_name = :media_format_name"; 
    }*/
    
    $sQuery .= " ORDER BY item_price_name ASC, price_local ASC";
    $query_result = db_query_range($sQuery, 0, 300, $aArgs);
    

    $aAllRows = array();
    while ( $a = $query_result->fetchAssoc() ) {
        $aAllRows[] = $a;
    } 
    db_set_active('default');
    return $aAllRows;
}


function getArtistMinPricesPerFormat_albums($artist_id, $currency )
{
	$sQuery = "
    SELECT artist.artist_id, media_format.media_format_name, artist.artist_name, item_base.item_base_name, item_base.item_year, 
    (price_local * currency_to_euro.to_euro * currency.from_euro) as price_MIN,
    record_store.record_store_name
    FROM artist
    INNER JOIN item_base ON item_base.artist_id = artist.artist_id 
    INNER JOIN item_price ON item_base.item_base_id = item_price.item_base_id 
    INNER JOIN media_format ON media_format.media_format_id = item_price.media_format_id
    INNER JOIN currency_to_euro ON currency_to_euro.currency_id = item_price.currency_id   
    INNER JOIN record_store ON record_store.record_store_id = item_price.record_store_id   
    INNER JOIN currency ON currency.currency_name = :currency_name   
    WHERE artist.artist_id = :artist_id AND item_base.item_type = 1
    ";
    $aArgs = array();
    $aArgs[':currency_name'] = $currency;
    $aArgs[':artist_id'] = $artist_id;

    db_set_active('airplay_music_v1');
    $query_result = db_query($sQuery,$aArgs);
    
    $aAllRows = array();
    while ( $a = $query_result->fetchAssoc() ) {
        $price_MIN = $a['price_MIN'];
        $media_format_name = $a['media_format_name'];
        if ( !array_key_exists( $media_format_name, $aAllRows ) ) {
            $aAllRows[$media_format_name] = $a;
        }
        else {
            if ( $aAllRows[$media_format_name]['price_MIN'] > $price_MIN ) {
             $aAllRows[$media_format_name] = $a;
            }
        }
    } 
    db_set_active('default');
    return $aAllRows;
}


function getArtistMinPricesPerFormat_songs($artist_id, $currency )
{
	$sQuery = "
    SELECT artist.artist_id, media_format.media_format_name, artist.artist_name, item_base.item_base_name, item_base.item_year, 
    (price_local * currency_to_euro.to_euro * currency.from_euro) as price_MIN,
    record_store.record_store_name
    FROM artist
    INNER JOIN item_base ON item_base.artist_id = artist.artist_id 
    INNER JOIN item_price ON item_base.item_base_id = item_price.item_base_id 
    INNER JOIN media_format ON media_format.media_format_id = item_price.media_format_id
    INNER JOIN currency_to_euro ON currency_to_euro.currency_id = item_price.currency_id   
    INNER JOIN record_store ON record_store.record_store_id = item_price.record_store_id   
    INNER JOIN currency ON currency.currency_name = :currency_name   
    WHERE artist.artist_id = :artist_id AND item_base.item_type = 2
    ";

    $aArgs = array();
    $aArgs[':currency_name'] = $currency;
    $aArgs[':artist_id'] = $artist_id;

    db_set_active('airplay_music_v1');
    $query_result = db_query($sQuery,$aArgs);
    
    $aAllRows = array();
    while ( $a = $query_result->fetchAssoc() ) {
        $price_MIN = $a['price_MIN'];
        $media_format_name = $a['media_format_name'];
        if ( !array_key_exists( $media_format_name, $aAllRows ) ) {
            $aAllRows[$media_format_name] = $a;
        }
        else {
            if ( $aAllRows[$media_format_name]['price_MIN'] > $price_MIN ) {
             $aAllRows[$media_format_name] = $a;
            }
        }
    } 
    db_set_active('default');

    return $aAllRows;
}

 
function getMinPricesPerFormat_albums($album_simple_id, $currency)
{
	$sQuery = "SELECT artist.artist_id, media_format.media_format_name, artist.artist_name, item_price.item_price_name, item_price.item_year, 
    (price_local * currency_to_euro.to_euro * currency.from_euro) as price_MIN,
    record_store.record_store_name
    FROM artist
    INNER JOIN item_base ON item_base.artist_id = artist.artist_id 
	INNER JOIN item_price ON item_base.item_base_id = item_price.item_base_id
	INNER JOIN record_store ON item_price.record_store_id = record_store.record_store_id
    INNER JOIN media_format ON media_format.media_format_id = item_price.media_format_id
    INNER JOIN currency_to_euro ON currency_to_euro.currency_id = item_price.currency_id   
    INNER JOIN currency ON currency.currency_name = :currency_name
	WHERE item_price.item_base_id = :item_base_id AND item_base.item_type = 1
	";
	
    $aArgs = array();
    $aArgs[':currency_name'] = $currency;
    $aArgs[':item_base_id'] = $album_simple_id;

    db_set_active('airplay_music_v1');
    $query_result = db_query($sQuery,$aArgs);
    
    $aAllRows = array();
    while ( $a = $query_result->fetchAssoc() ) {
        $price_MIN = $a['price_MIN'];
        $media_format_name = $a['media_format_name'];
        if ( !array_key_exists( $media_format_name, $aAllRows ) ) {
            $aAllRows[$media_format_name] = $a;
        }
        else {
            if ( $aAllRows[$media_format_name]['price_MIN'] > $price_MIN ) {
             $aAllRows[$media_format_name] = $a;
            }
        }
    } 
    db_set_active('default');

    return $aAllRows;
}



function getMinPricesPerFormat_songs($song_simple_id, $currency)
{
	$sQuery = "SELECT artist.artist_id, media_format.media_format_name, artist.artist_name, item_price.item_price_name, item_price.item_year, 
    (price_local * currency_to_euro.to_euro * currency.from_euro) as price_MIN,
    record_store.record_store_name
    FROM artist
    INNER JOIN item_base ON item_base.artist_id = artist.artist_id 
	INNER JOIN item_price ON item_base.item_base_id = item_price.item_base_id
	INNER JOIN record_store ON item_price.record_store_id = record_store.record_store_id
    INNER JOIN media_format ON media_format.media_format_id = item_price.media_format_id
    INNER JOIN currency_to_euro ON currency_to_euro.currency_id = item_price.currency_id   
    INNER JOIN currency ON currency.currency_name = :currency_name
	WHERE item_price.item_base_id = :item_base_id AND item_base.item_type = 2
	";

    $aArgs = array();
    $aArgs[':currency_name'] = $currency;
    $aArgs[':item_base_id'] = $song_simple_id;

    db_set_active('airplay_music_v1');
    $query_result = db_query($sQuery,$aArgs);
    
    $aAllRows = array();
    while ( $a = $query_result->fetchAssoc() ) {
        $price_MIN = $a['price_MIN'];
        $media_format_name = $a['media_format_name'];
        if ( !array_key_exists( $media_format_name, $aAllRows ) ) {
            $aAllRows[$media_format_name] = $a;
        }
        else {
            if ( $aAllRows[$media_format_name]['price_MIN'] > $price_MIN ) {
             $aAllRows[$media_format_name] = $a;
            }
        }
    } 
    db_set_active('default');

    return $aAllRows;
}

function getCoverImageInfo_albums($item_base_id)
{
	$sQuery = "SELECT image_url, image_width, image_height
					FROM item_base
					WHERE item_base_id = :item_base_id";
	
    $aArgs = array();
    $aArgs[':item_base_id'] = $item_base_id;

    db_set_active('airplay_music_v1');
    $query_result = db_query($sQuery,$aArgs);
	
    $aAllRows = array();
    while ( $a = $query_result->fetchAssoc() ) {
        $aAllRows[] = $a;
    } 
    db_set_active('default');
	
    return $aAllRows;
}


function getArtistInfo( $artist_id )
{
    db_set_active('airplay_music_v1');
    $sQuery = "SELECT artist_real_name, gender, artist_type, year_start, year_end, year_born, year_died, country_name, url_artist_official, url_fanpage, url_wikipedia, url_allmusic, url_musicbrainz, url_discogs, url_facebook FROM info_artist LEFT OUTER JOIN country ON country.country_id = info_artist.country_id WHERE artist_id = $artist_id";
    $query_result = db_query( $sQuery, array() );
    $aAllRows = array();
    while ( $a = $query_result->fetchAssoc() ) {
        $aAllRows[] = $a;
    } 
    db_set_active('default');
    return $aAllRows;
}

function artistIdFromName( $artist_name ) 
{
    $id = 0;
    db_set_active('airplay_music_v1');
    
    $query = "
        SELECT artist_id 
        FROM {artist} 
        WHERE artist_name = :artist_name 
        ";
    $query_result =  db_query($query, array( ':artist_name' => $artist_name) );
    if ( $a = $query_result->fetchAssoc() ) {
        $id = $a['artist_id'];
    }
    db_set_active('default');
    return $id;
}

function itemSimpleIdFromNameExact( $artist_id, $item_type, $item_simple_name  ) 
{
    $id = 0;
    db_set_active('airplay_music_v1');
    /*$query = "
        SELECT {$item_type}_simple_id  
        FROM {$item_type}_simple 
        WHERE {$item_type}_simple_name = :item_simple_name AND artist_id = :artist_id
    ";*/
	
	if ($item_type == "album") {
	$query = "
        SELECT item_base_id 
        FROM item_base
        WHERE item_base_name = :item_base_name AND artist_id = :artist_id AND item_base.item_type = 1
    ";
	} else if ($item_type == "song") {
	$query = "
        SELECT item_base_id 
        FROM item_base
        WHERE item_base_name = :item_base_name AND artist_id = :artist_id AND item_base.item_type = 2
    ";
	}
    
    $query_result =  db_query($query, array( ':item_base_name' => $item_simple_name, ':artist_id' => $artist_id) );
    if ( $a = $query_result->fetchAssoc() ) {
        $id = $a["item_base_id"];
    }
    db_set_active('default');
    return $id;
}


// http://php.net/manual/en/function.str-replace.php
function lookupDescriptionArtist( $artist_id, $languageCode )
{
    $artist_article = "";
    db_set_active('airplay_music_v1');
    
    $q = "SELECT artist_article FROM artist_text WHERE artist_id = :artist_id AND language_code = :language_code";
    $query_result =  db_query($q, array( ':artist_id' => $artist_id, ':language_code' => $languageCode));
    
    $a = $query_result->fetchAssoc();
    $artist_article = $a['artist_article'];
    if ( "" == $artist_article ) {
        $q = "SELECT artist_article FROM artist_text WHERE language_code = :language_code";
        $query_result =  db_query($q, array( ':language_code' => $languageCode));
        $a = $query_result->fetchAssoc();
    }
    $artist_article = $a['artist_article'];
    db_set_active('default');

    return $artist_article;
}

/* Function to get parent for song aka get album for a song */
function lookupParent_song( $item_base_id ) {
	$tracknames = array();
	db_set_active('airplay_music_v1');
    
    $query = "SELECT item_base_name FROM item_base WHERE item_base_id = (SELECT parent_item FROM item_base WHERE item_base_id = :item_base_id AND item_base_id <> parent_item)";
    $query_result =  db_query($query, array( ':item_base_id' => $item_base_id));
	
	while ( $a = $query_result->fetchAssoc() ) {
        $tracknames[] = array ( 'item_base_name' => $a['item_base_name']);
    }
    db_set_active('default');

    return $tracknames;
}

function getSongParentHTML($tracklist, $artist_name) {
	if ( count($tracklist) > 0 ) {
		$sHtml = "<div class='album' style='float: left;'>";
		$sHtml .= "<div class='header' style='margin-top:10px;margin-bottom:10px;margin-right:10px;font-weight:bold;float: left;'>Sang findes på albummet:</div>";
		foreach ( $tracklist as $item_base_name => $a ) {
			$albumname = $a["item_base_name"];
			$sHtml .= "<div class='album' style='float: left;margin-top:10px;margin-bottom:10px;'><a href='/" . ap_artist_suburl() . "/" . airplay_name_to_url($artist_name) . "/" . ap_album_suburl() . "/" . airplay_name_to_url($albumname) . "' title='{$albumname}'>{$albumname}</a></div>";
		}
		$sHtml .= "</div>";
	} else {
		$sHtml = "";
	}
	return $sHtml;
}

function lookupTracklist_album( $item_base_id )
{

	$tracknames = array();
	db_set_active('airplay_music_v1');

    $query = "SELECT item_base_name, track_number FROM item_base WHERE parent_item = :item_base_id AND ( SELECT count(*) AS number_of_tracks FROM item_base WHERE parent_item = :item_base_id ) < 15 ORDER BY track_number asc";

    $query_result =  db_query($query, array( ':item_base_id' => $item_base_id));
	
	while ( $a = $query_result->fetchAssoc() ) {
        $tracknames[] = array ( 'item_base_name' => $a['item_base_name'], 'track_number' => $a['track_number']);
    }
    db_set_active('default');

    return $tracknames;
}

function getTracklistHTML($tracklist, $artist_name, $item_name) {
	$header_text = t("Tracklist for the album {album_name} by {artist_name}:");
	$header_text = str_replace("{artist_name}", $artist_name, $header_text);
	$header_text = str_replace("{album_name}", $item_name, $header_text);
	$valid_track_list = true;
	$tracklist_count = count($tracklist);
	// Test if it is a valid tracklist "hack" tracks are nummeric order like 1, 2, 3, 4, 5, and under 15 tracks. - NOT valid are 1, 2, 4, 4
	if ( $tracklist_count > 0 && $tracklist_count < 15) {
		
		for ( $i = 0; $i < $tracklist_count; $i++ ) {
			if ($i != ((int)$tracklist[$i]["track_number"]-1)) {
				$valid_track_list = false;
				break;
			}
		}
		}
	
	if ( $tracklist_count > 0 && $valid_track_list == true) {
		//$sHtml = "<div class='tracklist'>";
		$sHtml .= "<h2>{$header_text}</h2>";
		// OPTIMIZE  - No need 2 loops
		$item_pr_block = ceil($tracklist_count/2);
		$counter = 1;
		$sHtml .= "<div class='left'>";
		foreach ( $tracklist as $item_base_name => $a ) {
			if ($counter <= $item_pr_block) {
				$trackname = $a["item_base_name"];
				if ($a["track_number"] < 10) {
					$track_number = "0" . $a["track_number"];
				} else {
					$track_number = $a["track_number"];
				}
				$sHtml .= "<div class='track'>" . $track_number . " <a href='/" . ap_artist_suburl() . "/" . airplay_name_to_url($artist_name) . "/" . ap_song_suburl() . "/" . airplay_name_to_url($trackname) . "' title='". htmlentities($trackname) . "'>". htmlentities($trackname) . "</a></div>";
			}
			$counter++;
		}
		$sHtml .= "</div>";
		$sHtml .= "<div class='right'>";
		// OPTIMIZE  - No need 2 loops
		$counter = 1;
		foreach ( $tracklist as $item_base_name => $a ) {
			if ($counter > $item_pr_block) {
				$trackname = $a["item_base_name"];
				if ($a["track_number"] < 10) {
					$track_number = "0" . $a["track_number"];
				} else {
					$track_number = $a["track_number"];
				}
				$sHtml .= "<div class='track'>" . $track_number . " <a href='/" . ap_artist_suburl() . "/" . airplay_name_to_url($artist_name) . "/" . ap_song_suburl() . "/" . airplay_name_to_url($trackname) . "' title='". htmlentities($trackname) . "'>". htmlentities($trackname) . "</a></div>";
			}
			$counter++;
		}
		$sHtml .= "</div>";
		//$sHtml .= "</div>";
	} else {
		$sHtml = "";
	}
	return $sHtml;
}


function lookupDescriptionWikiArtist( $artist_id, $languageCode )
{
	$wiki_text = "";
	db_set_active('airplay_music_v1');
    
    $query = "
        SELECT wiki_text FROM wiki_text_artist WHERE language_code = :language_code AND artist_id = :artist_id
        ";
    $query_result =  db_query($query, array( ':artist_id' => $artist_id, ':language_code' => $languageCode));
	
	/* While overrides the default values */
    while ( $a = $query_result->fetchAssoc() ) {
        $wiki_text = $a['wiki_text'];
    }
    db_set_active('default');

    return $wiki_text;
}



function getDescriptionArtist( $sBaseDesc, $aReplaceArray )
{
    $aLookFor = array ( "{artist_name}" );
    return str_replace ( $aLookFor, $aReplaceArray, $sBaseDesc );    
}


function lookupDescription_album( $artist_id, $album_simple_id, $languageCode )
{
	$intro_text = "";
	db_set_active('airplay_music_v1');
    
    $query = "
        SELECT intro_text FROM intro_text_album WHERE is_default = 1 AND language_code = :language_code
		UNION
		SELECT intro_text FROM intro_text_album WHERE is_default = 0 AND language_code = :language_code AND album_simple_id = :album_simple_id
        ";
    $query_result =  db_query($query, array( ':album_simple_id' => $album_simple_id, ':language_code' => $languageCode));
	
	/* While overrides the default values */
    while ( $a = $query_result->fetchAssoc() ) {
        $intro_text = $a['intro_text'];
    }
    db_set_active('default');

    return $intro_text;
}

function getDescription_album( $sBaseDesc, $aReplaceArray )
{
    $aLookFor = array ( "{artist_name}", "{artist_url}", "{album_name}", "{album_url}" );
    return str_replace ( $aLookFor, $aReplaceArray, $sBaseDesc );    
}


function lookupDescription_song( $artist_id, $song_simple_id, $languageCode )
{
	$intro_text = "";
	db_set_active('airplay_music_v1');
    
    $query = "
        SELECT intro_text FROM intro_text_song WHERE is_default = 1 AND language_code = :language_code
		UNION
		SELECT intro_text FROM intro_text_song WHERE is_default = 0 AND language_code = :language_code AND song_simple_id = :song_simple_id
        ";
    $query_result =  db_query($query, array( ':song_simple_id' => $song_simple_id, ':language_code' => $languageCode));
	
	/* While overrides the default values */
    while ( $a = $query_result->fetchAssoc() ) {
        $intro_text = $a['intro_text'];
    }
    db_set_active('default');

    return $intro_text;
}

function getDescription_song( $sBaseDesc, $aReplaceArray )
{
    $aLookFor = array ( "{artist_name}", "{artist_url}", "{song_name}", "{song_url}" );
    return str_replace ( $aLookFor, $aReplaceArray, $sBaseDesc );    
}

function getItemTypeCountFromArtist ($artist_id) {
	db_set_active('airplay_music_v1');
    
    $query = "SELECT COUNT(*) AS item_type_count, item_type FROM item_price WHERE artist_id = :artist_id AND item_type IN (1, 2, 3) GROUP BY item_type";
    $query_result =  db_query($query, array( ':artist_id' => $artist_id));
	$aAllRows = array();
    while ( $a = $query_result->fetchAssoc() ) {
        if ($a['item_type'] == 1) {
            $item_type = $a['item_type_count'];
            $item_type = 'item_album_type';
        } else if ($a['item_type'] == 2) {
            $item_type = $a['item_type_count'];
            $item_type = 'item_song_type';
        } else if ($a['item_type'] == 3) {
            $item_type = $a['item_type_count'];
            $item_type = 'item_merchandise_type';
        } else if ($a['item_type'] == 4) {
            $item_type = $a['item_type_count'];
            $item_type = 'item_concert_type';
        }
        
        $aAllRows[$item_type] = $a;
    } 
    db_set_active('default');
    
    return $aAllRows;
}

function getItemTypeCountFromArtistByType ($artist_id, $item_type) {
	db_set_active('airplay_music_v1');
    
    $query = "SELECT COUNT(*) AS item_type_count FROM item_price WHERE artist_id = :artist_id AND item_type = :item_type";
	if ($item_type == 4) {
		$query .= " AND  (item_event_date >= CURDATE() OR item_event_date = '0000-00-00') AND timestamp_updated >= DATE_ADD(now(), INTERVAL -10 DAY)";
	}
    $query_result =  db_query($query, array( ':artist_id' => $artist_id, ':item_type' => $item_type));
	$count = 0;
    while ( $a = $query_result->fetchAssoc() ) {
        $count = $a['item_type_count'];
    } 
    db_set_active('default');

    return $count;
}

function getItemTypePriceCountFromArtist ($artist_id) {
	db_set_active('airplay_music_v1');
    
    $query = "SELECT COUNT(*) AS item_type_count, item_type FROM item_price WHERE artist_id = :artist_id GROUP BY item_type";
    $query_result =  db_query($query, array( ':artist_id' => $artist_id));
	$aAllRows = array();
    while ( $a = $query_result->fetchAssoc() ) {
        if ($a['item_type'] == 1) {
            $item_type = $a['item_type_count'];
            $item_type = 'item_album_type';
        } else if ($a['item_type'] == 2) {
            $item_type = $a['item_type_count'];
            $item_type = 'item_song_type';
        } else if ($a['item_type'] == 3) {
            $item_type = $a['item_type_count'];
            $item_type = 'item_merchandise_type';
        } else if ($a['item_type'] == 4) {
            $item_type = $a['item_type_count'];
            $item_type = 'item_concert_type';
        }
        
        $aAllRows[$item_type] = $a;
    } 
    db_set_active('default');
    
    return $aAllRows;
}

function lookupReview_data( $item_base_id, $languageCode )
{
	$aAllRows = array();
	db_set_active('airplay_music_v1');
    $query = "SELECT review_rating, review_url, review_text, image_full_path, image_blank_path, image_half_path, link_text, has_half_value, layout_type, rating_max_value,  review_text_chars
	FROM item_base_review
	INNER JOIN record_store ON record_store.record_store_id = item_base_review.record_store_id
	INNER JOIN record_store_review_settings ON record_store_review_settings.record_store_id = record_store.record_store_id
	WHERE item_base_id = :item_base_id AND show_on_sites LIKE :languageCode
	ORDER BY sort_order ASC";
    $query_result =  db_query($query, array( ':item_base_id' => $item_base_id, ':languageCode' => '%' . $languageCode . '%'));
	/* While overrides the default values */
    while ( $a = $query_result->fetchAssoc() ) {
		$aAllRows[] = $a;
    }
    db_set_active('default');
	return $aAllRows;
}


function getCurrencyCodesFromToValues() {
    db_set_active('airplay_music_v1');
    
    $query = "SELECT currency_name, from_euro, to_euro FROM currency;";
    $query_result =  db_query($query, array());
	$aAllRows = array();
    while ( $a = $query_result->fetchAssoc() ) {
        $aAllRows[] = array("currency_name" => $a["currency_name"], "from_euro" => $a["from_euro"], "to_euro" => $a["to_euro"]);
    } 
    db_set_active('default');
    
    return $aAllRows;
}

function lookupItemVideo_data($item_base_id) {
	$aAllRows = array();
	db_set_active('airplay_music_v1');
    $query = "SELECT embed_html FROM video WHERE item_base_id = :item_base_id";
    $query_result =  db_query($query, array( ':item_base_id' => $item_base_id));
    while ( $a = $query_result->fetchAssoc() ) {
		$aAllRows = $a;
    }
    db_set_active('default');
	return $aAllRows;
}

function insertItemVideo_data($item_base_id, $embed_html) {
	$aAllRows = array();
	$ip_address = $_SERVER['REMOTE_ADDR'];
	db_set_active('airplay_music_v1');
    db_insert('video')->fields(array('item_base_id' => $item_base_id, 'artist_id' => 0, 'embed_html' => $embed_html, 'ip_address' => $ip_address))->execute();
    db_set_active('default');
	return $aAllRows;
}

function lookupArtistVideo_data($artist_id) {
	$aAllRows = array();
	db_set_active('airplay_music_v1');
    $query = "SELECT embed_html FROM video WHERE artist_id = :artist_id";
    $query_result =  db_query($query, array( ':artist_id' => $artist_id));
    while ( $a = $query_result->fetchAssoc() ) {
		$aAllRows = $a;
    }
    db_set_active('default');
	return $aAllRows;
}

function insertArtistVideo_data($artist_id, $embed_html) {
	$aAllRows = array();
	$ip_address = $_SERVER['REMOTE_ADDR'];
	db_set_active('airplay_music_v1');
    db_insert('video')->fields(array('item_base_id' => 0, 'artist_id' => $artist_id, 'embed_html' => $embed_html, 'ip_address' => $ip_address))->execute();
    db_set_active('default');
	return $aAllRows;
}