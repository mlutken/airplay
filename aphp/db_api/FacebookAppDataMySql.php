<?php

require_once ("db_api/SimpleTableDataMySql.php");


class FacebookAppDataMySql extends SimpleTableDataMySql
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $dbPDO = null )
    {
        parent::__construct( 
        'facebook_app_v1'
        , array(  'facebook_app_v1_id', 'timestamp_updated', 'artist_id', 'item_type', 'media_format_id', 'json' ) 
        , $dbPDO );
    }
	    
    /** Get all data needed to display an artist page used in our facebook app */
    public function getFacebookArtistPageData ($artist_id, $item_type, $media_format_id, $currency_code)
    {
		// Album or song.
		if ($item_type == 1 || $item_type == 2) {
		$q = "
		SELECT artist.artist_id, artist.artist_name, item_base.item_base_name, COUNT(*) as item_prices_count, media_format.media_format_name, item_base.release_date,
		MIN(price_local * currency_to_euro.to_euro * currency.from_euro) as min_price_local, MAX(price_local * currency_to_euro.to_euro * currency.from_euro) as max_price_local
		FROM artist
		LEFT JOIN item_base ON item_base.artist_id = artist.artist_id 
		LEFT JOIN item_price ON item_base.item_base_id = item_price.item_base_id 
		LEFT JOIN media_format ON media_format.media_format_id = item_price.media_format_id
		INNER JOIN currency_to_euro ON currency_to_euro.currency_id = item_price.currency_id
		INNER JOIN currency ON currency.currency_name = ?";
		$q .= " WHERE artist.artist_id = ? AND item_base.item_type = ?";
		if ($media_format_id != 0) {
			$q .= " AND media_format.media_format_id = ?"; 
		} else {
			$q .= " AND 0 = ?"; 
		}
		if ($item_type == 1) {
			$q .= " GROUP BY item_base_name ORDER BY item_base.release_date DESC, item_base_name ASC";
		} else {
			$q .= " GROUP BY item_base_name ORDER BY item_base_name ASC";
		}
		// Merchandise
		} else if ($item_type == 3) {
		$q = "
			SELECT item_price_name,
			(price_local * currency_to_euro.to_euro * currency.from_euro) as price_local,   
			media_format.media_format_name, item_price.buy_at_url, record_store.use_affiliate,
			record_store.affiliate_link, record_store.affiliate_encode_times, record_store.record_store_name
			FROM artist
			INNER JOIN item_base ON item_base.artist_id = artist.artist_id 
			INNER JOIN item_price ON item_base.item_base_id = item_price.item_base_id 
			INNER JOIN media_format ON media_format.media_format_id = item_price.media_format_id
			INNER JOIN currency_to_euro ON currency_to_euro.currency_id = item_price.currency_id
			INNER JOIN record_store ON item_price.record_store_id = record_store.record_store_id
			INNER JOIN currency ON currency.currency_name = ?
			WHERE artist.artist_id = ? AND item_base.item_type = ?";
			if ($media_format_id != 0) {
				$q .= " AND media_format.media_format_id = ?"; 
			} else {
				$q .= " AND 0 = ?"; 
			}
			$q .= " ORDER BY price_local ASC";
		// Concerts
		} else if ($item_type == 4) {
		$q = "
			SELECT item_event_date, item_event_time, item_price_name,
			(price_local * currency_to_euro.to_euro * currency.from_euro) as price_local,   
			media_format.media_format_name, item_price.buy_at_url, record_store.use_affiliate,
			record_store.affiliate_link, record_store.affiliate_encode_times, record_store.record_store_name
			FROM artist
			INNER JOIN item_base ON item_base.artist_id = artist.artist_id 
			INNER JOIN item_price ON item_base.item_base_id = item_price.item_base_id 
			INNER JOIN media_format ON media_format.media_format_id = item_price.media_format_id
			INNER JOIN currency_to_euro ON currency_to_euro.currency_id = item_price.currency_id
			INNER JOIN record_store ON item_price.record_store_id = record_store.record_store_id
			INNER JOIN currency ON currency.currency_name = ?
			WHERE item_event_date >= CURDATE() AND artist.artist_id = ? AND item_base.item_type = ?";
			if ($media_format_id != 0) {
				$q .= " AND media_format.media_format_id = ?"; 
			} else {
				$q .= " AND 0 = ?"; 
			}
			$q .= " GROUP BY item_price_name, item_event_date ORDER BY item_event_date ASC, item_event_time ASC";
		}

		return pdoQueryAssocRows($this->m_dbPDO, $q, array($currency_code, $artist_id, $item_type, $media_format_id));
   }
	
   /** Get all data needed to display an item page used in our facebook app */
    public function getFacebookItemPageData ($item_base_id, $media_format_id, $currency_code)
    {
		$q = "
			SELECT record_store.record_store_id, item_price_name, 
			(price_local * currency_to_euro.to_euro * currency.from_euro) as price_local,
			record_store.record_store_name, currency.currency_name, 
			media_format.media_format_name, item_price.buy_at_url, record_store.use_affiliate,
			record_store.affiliate_link, record_store.affiliate_encode_times, item_used, item_grading, item_grading_cover
			FROM item_price
			INNER JOIN media_format ON media_format.media_format_id = item_price.media_format_id
			INNER JOIN record_store ON item_price.record_store_id = record_store.record_store_id
			INNER JOIN currency_to_euro ON currency_to_euro.currency_id = item_price.currency_id   
			INNER JOIN currency ON currency.currency_name = ?";
		$q .= " WHERE item_base_id = ? ";
		if ($media_format_id != 0) {
			$q .= " AND media_format.media_format_id = ?"; 
		} else {
			$q .= " AND 0 = ?"; 
		}
		$q .= "  ORDER BY price_local ASC";

		return pdoQueryAssocRows($this->m_dbPDO, $q, array($currency_code, $item_base_id, $media_format_id));
   }
   
	/*
		Function used to see if data is in facebook cache
	*/
	public function getItemBasePriceFacebookAppDataFromCache($artist_id, $media_format_id, $item_type)
	{
		$q = "SELECT json, TIMESTAMPDIFF(MINUTE, timestamp_updated, now()) AS time_span, facebook_app_v1_id FROM facebook_app_v1 WHERE artist_id = ? AND item_type = ? AND media_format_id = ?";
		return pdoQueryAssocRows($this->m_dbPDO, $q, array($artist_id, $item_type, $media_format_id));
	}
	
	/*
		Function used to see if data is in facebook cache
	*/
	public function updateItemBasePriceFacebookAppData($facebook_app_v1_id, $json)
	{
		$stmt = $this->m_dbPDO->prepare("UPDATE facebook_app_v1 SET json = :json, timestamp_updated = now() WHERE facebook_app_v1_id = :facebook_app_v1_id" );
		$stmt->execute( array( ":facebook_app_v1_id" => $facebook_app_v1_id, ":json" => $json) );
        $result += $stmt->rowCount();
        return $result;
	}
	
	/*
		Function used to get data to show on Facebook 
	*/
	public function getItemBasePriceFacebookAppData ($artist_id, $media_format_id, $item_type)
    {
		$q = "
		SELECT item_base_name, buy_at_url, record_store_name, affiliate_link, affiliate_encode_times, media_format.media_format_name, (price_local*100) AS price_local, currency_name AS currency_code
		FROM item_price
		INNER JOIN item_base ON item_base.item_base_id = item_price.item_base_id
		INNER JOIN media_format ON media_format.media_format_id = item_price.media_format_id
		INNER JOIN record_store ON record_store.record_store_id = item_price.record_store_id
		INNER JOIN currency ON currency.currency_id = item_price.currency_id
		WHERE record_store.record_store_id IN (3, 8, 19, 23, 41, 42, 54, 61, 140) AND item_price.artist_id = ? AND item_price.item_type = ?";
		if ($media_format_id != 0) {
			$q .= " AND media_format.media_format_id = ?"; 
		} else {
			$q .= " AND 0 = ?"; 
		}
		$q .= "  GROUP BY item_base_name , record_store_name";

		return pdoQueryAssocRows($this->m_dbPDO, $q, array($artist_id, $item_type, $media_format_id));
	}
	
	/*
		Function used to insert data to show on Facebook 
	*/
	public function insertItemBasePriceFacebookAppData($artist_id, $media_format_id, $item_type, $ap_item_data) {
        $stmt = $this->m_dbPDO->prepare("INSERT INTO facebook_app_v1 (artist_id, media_format_id, item_type, json) VALUES ( ?, ?, ?, ? )" );
        $stmt->execute( array($artist_id, $media_format_id, $item_type, $ap_item_data) );
        $id = (int)$this->m_dbPDO->lastInsertId();
        return $id;
	}

    // --------------------------
    // --- PRIVATE: Functions --- 
    // --------------------------
    
    // ---------------------
    // --- PRIVATE: Data --- 
    // ---------------------
    static          $m_aTblFields = array( 'facebook_app_v1_id', 'timestamp_updated', 'artist_id', 'item_type', 'media_format_id', 'json' );
    
}


?>
