<?php

require_once ("filedb_api/ChildTableDataFileDb.php");


class ItemBaseDataFileDb extends ChildTableDataFileDb
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $artistDataFileDb )
    {
        parent::__construct( $artistDataFileDb );
    }

    // -------------------------------------------------
    // --- FileDb functions to override/re-implement --- 
    // -------------------------------------------------

    /** Get leaf name of json file from id.
    \return Path to json data file.  
    \note MUST implement this in derived class! */
    public function leafFileName ()
    {
		return $this->m_id . '.json';
    }
    
    
    // -------------------------------------
    // --- Erase/delete/merge functions ----
    // -------------------------------------

//     /** . */
//     public function merge ($fromItemBaseDataFileDb)
//     {
//     }
//     
//     /** Move item_base to a new artist "owner". I.e. change the artist to which the \a $from_item_base_id belongs */
//     public function moveToArtist ($fromItemBaseDataFileDb)
//     {
//     }

   
    // ----------------------------------
    // --- Item ID lookup functions --- 
    // ----------------------------------

//     /** Lookup item ID from item "base/real/official" name.
//     \return one item ID if found one and only one matching. Zero if not found any or more than one found.  */
//     public function nameToID ($artist_id, $item_base_name, $item_type)
//     {
//     }
    
//     /** Lookup ID from $aData.
//     \return One ID if found one and only one matching. Zero if not found any or more than one found.  */
//     public function toID ($aData)
//     {
//     }
    
    /** Lookup item ID from item "base/real/official" name.
    \deprecated Use nameToID
    \return one item ID if found one and only one matching. Zero if not found any or more than one found.  */
    public function baseNameToID ($artist_id, $item_base_name, $item_type)
    {
    }
    
    /** TODO: Doc finish
    Exact match using soundex strings . */
    public function findID ( $artist_id, $item_name_raw, $item_type, $dbItemBaseCorrection )
    {
    }

    /** TODO: Doc finish
    Fuzzy match using soundex strings. We consider it a match if the string are at least "90% alike"
    - Which in this context is computed from the if levenshtein distance between the cleanedItemName 
    and each candidate item name. We get a factor like this:
        $levDistSoundex = levenshtein ( $soundexToFind , $soundexCandidate );
        $fac  = 1.0 - ($levDistSoundex / $iLenSoundexToFind);
        The best matching is returned, but only if is greater than .90 (90% match).
    */
    public function fuzzyFindID ( $artist_id, $item_name_raw, $item_type, $dbItemBaseCorrection )
    {
    }

    // ------------------------------
    // --- Names lookup functions --- 
    // ------------------------------
    public function lookupSimilarNames ( $name )
    {
    }

    
    // --------------------------
    // --- Get data functions --- 
    // --------------------------
    /** Get item base data,. Info like name, genre, wiki info etc. */
    public function getBaseData ($item_base_id)
    {
    }
    
    /** Get item_base official name from ID. 
    \return Official AP name. */
    public function IDToName ($item_base_id)
    {
    }

       
    /** Get child song base_item IDs. The \a $item_base_id should be an album, otherwise an emty list is returned. */
    public function getChildSongIDs ($item_base_id)
    {
    }

    
    // -----------------------------------------------
    // --- Get data functions needing an artist_id ---
    // -----------------------------------------------
    
    /** Get artist base item IDs. */
    public function getItemBaseIDs ($artist_id, $item_type)
    {
    }

    /** Get artist base album IDs. */
    public function getBaseAlbumIDs ($artist_id)
    {
    }
    
    /** Get artist base song IDs. */
    public function getBaseSongIDs ($artist_id)
    {
    }
    
    
    /**  */
    public function getItemsForArtist ( $artist_id, $item_type )
    {
    }
    
    
    /** Get all data needed to display an item page */
    public function getPageData ($item_base_id)
    {
        // TODO: Implement this!
    }
    
    
    // --------------------------
    // --- Set data functions --- 
    // --------------------------
    /**  Set base data of item. Creates new item if name not found. */
    public function setBaseData ($aData)
    {

    }
    
         
    // -------------------------------
    // --- Create/update functions --- 
    // -------------------------------
    /** 
     */
    public function createNew ( $aBaseData, $item_base_id, $parentArtistDataFileDb_UNUSED ) 
    {
		if ( !isset($item_base_id) || '' == $item_base_id ) {
			$item_base_id = nameToID( $aBaseData['item_base_name'] );
		}
		$a['item_base_id'] = $item_base_id;
		$a['item_base_name'] = $aBaseData['item_base_name'];
		$a['item_type'] = $aBaseData['item_type'];
		
 		$this->m_aAllData['base_data'] = $a;
// // 		$this->m_aAllData['base_data'] = $aBaseData;
		$this->m_aAllData['item_prices'] = array();
		$this->m_aAllData['text'] = array();
    }
    
    /** Unconditionally update the base data.
     */
    public function updateBaseData ( $aBaseData ) 
    {
		// TODO: Figure out which approach is the fastest!
		$this->doUpdateBaseData( $aBaseData, ItemBaseDataFileDb::$M_aBaseDataFields );
		//$this->m_aAllData['base_data'] = updateAssocData( $this->m_aAllData['base_data'], $aBaseData, ItemBaseDataFileDb::$M_aBaseDataFields );
    }
	

    /** Update the base data using reliability.
     */
    public function updateBaseDataCheckReliability ( $aBaseData ) 
    {
		// TODO: Figure out which approach is the fastest!
		$this->doUpdateBaseDataCheckReliability( $aBaseData, ItemBaseDataFileDb::$M_aBaseDataFields, 'item_base_reliability' );
// 		$this->m_aAllData['base_data'] = updateAssocDataCheckReliability( $this->m_aAllData['base_data'], $aBaseData, ItemBaseDataFileDb::$M_aBaseDataFields, 'artist_reliability' );
    }

    // --------------------------------------
    // --- ItemBase text/article functions ----
    // --------------------------------------

    /**  Update/create article/text for a given language_code
    */
    public function updateText ( $language_code, $item_base_article, $item_base_text_reliability )
    {
		if ( '' == $language_code || '' == $item_base_article ) return;
		$reliabilityOld = (int)$this->m_aAllData['text'][$language_code]['item_base_text_reliability'];
		$reliabilityNew = (int)$item_base_text_reliability;
		$bNewDataBetter = $reliabilityNew > $reliabilityOld;

		if ( $bNewDataBetter || '' == $this->m_aAllData['text'][$language_code]['item_base_article'] ) {
			$this->m_aAllData['text'][$language_code]['item_base_article'] = $item_base_article;
			$this->m_aAllData['text'][$language_code]['item_base_text_reliability'] = $item_base_text_reliability;
		}
	}

    
    // -----------------------
    // --- Price functions ---
    // -----------------------
    public function updatePrice( $aItemPrice, $currencyConvert, $ts )
    {
		$id = '';
		$price_local    	= (float)$aItemPrice['price_local'];
		$currency_id    	= $aItemPrice['currency_id'];	// DKK, USD, EUR, ...
		
		if ( $price_local > 0 && '' != $currency_id ) 
		{
			$id = createItemPriceIDFromData($aItemPrice);
			if ( '' != $id ) {
				$aItemPrice['price_euro'] = $currencyConvert->toEuro( $currency_id, $price_local );
				$aItemPrice['timestamp_updated'] = $ts;
				$this->m_aAllData['item_prices'][$id] = $aItemPrice;
			}
		}
		return $id;
    }
    
    
    /** Get artist base item IDs. */
    public function getPriceDataForArtist ( $currencyConvert )
    {
		$price_euro_min =  PHP_INT_MAX;
		$price_euro_max =  PHP_INT_MIN; // Defined in airplay_globals.php
		$aPriceData = array();
		$i = 0;
		if ( !isset( $this->m_aAllData) || !array_key_exists('item_prices', $this->m_aAllData ) ) return array();
		foreach ( $this->m_aAllData['item_prices'] as $item_price_id => $aPrice ) {
			$media_format_id = $aPrice['media_format_id'];
			$i++;
			$count = (int) $aPriceData[$media_format_id];
			$aPriceData[$media_format_id] = $count +1;
			if ( 4 != $media_format_id ) { // Only calc min/max fon non-streming media formats
				$price_euro = $currencyConvert->toEuro( $aPrice['currency_id'], $aPrice['price_local'] );
				//printf("price_local: {$aPrice['price_local']} {$aPrice['currency_id']}, price_euro: $price_euro PHP_INT_MIN: %d\n", PHP_INT_MIN );
				if ( 0 < $price_euro ) {
					if ( $price_euro < $price_euro_min ) $price_euro_min = $price_euro;
					if ( $price_euro > $price_euro_max ) $price_euro_max = $price_euro;
				}
			}
				
		}
		if ( PHP_INT_MAX == $price_euro_min ) $price_euro_min = 0;
		if ( PHP_INT_MIN == $price_euro_max ) $price_euro_max = 0;
		$aPriceData['price_euro_min'] = $price_euro_min;
		$aPriceData['price_euro_max'] = $price_euro_max;
		$aPriceData['total'] = $i;
		return $aPriceData;
    }

    
    
    // --------------------------
    // --- PRIVATE: Functions --- 
    // --------------------------
    
    // ---------------------
    // --- PRIVATE: Data --- 
    // ---------------------

	private static    $M_aBaseDataFields = array(  
					'item_type', 'artist_id', 'record_label_name', 'item_genre_id'
					, 'item_subgenre_ids', 'item_year', 'release_date', 'parent_item_id', 'parent_item_name'
					, 'parent_item_type', 'item_time', 'track_number', 'child_items', 'item_base_reliability'
					);
// // 	private static    $M_aBaseDataFields = array(  
// // 					'item_type', 'artist_id', 'item_base_name', 'record_label_name', 'item_genre_id'
// // 					, 'item_subgenre_ids', 'item_year', 'release_date', 'parent_item_id', 'parent_item_name'
// // 					, 'parent_item_type', 'item_time', 'track_number', 'child_items', 'item_base_reliability'
// // 					);
}


?>
