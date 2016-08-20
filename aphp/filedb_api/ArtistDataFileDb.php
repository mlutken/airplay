<?php

require_once ('filedb_api/ParentTableDataFileDb.php');
require_once ('filedb_api/ItemBaseDataFileDb.php');

// 'artist_genre_id'

class ArtistDataFileDb extends ParentTableDataFileDb
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $fileDbBaseDir, $currencyConvert )
    {
        parent::__construct('artist', $fileDbBaseDir );
        $this->m_currencyConvert = $currencyConvert;
    }

 
 
    // ------------------------------------------------------------
    // --- TableDataFileDbIF functions to override/re-implement --- 
    // ------------------------------------------------------------
    public function idExists ($id, $idHash32) 
    {
		if ( null == $idHash32 ) $idHash32 = hash32($id); 
		$moduloDir = moduloDirFromHash32($idHash32); 
		$relativeFileDir  = 'artist/' . $moduloDir . '/' . $id;
		
		$fullArtistFilePath =  $this->m_fileDbBaseDir . '/' . $relativeFileDir . '/artist.json';
		return file_exists($fullArtistFilePath);
    }

    /** Get leaf name of json file from id.
    \return Path to json data file.  
    \note MUST implement this in derived class! */
    public function leafFileName ()
    {
		return 'artist.json';
    }
    
    /** Get relative (from base dir) path to directory on disk.
    \return Path directory on disk. 
    \note MUST implement this in derived class! */
    public function relativeFileDir ()
    {
		// Use pre-calculated hash32 if available
		if ( null == $this->m_idHash32 ) $this->m_idHash32 = hash32($this->m_id); 
		$moduloDir = moduloDirFromHash32($this->m_idHash32); 
		$artistDir    = 'artist/' . $moduloDir . '/' . $this->m_id;
		return $artistDir;
    }
    

    // ----------------------------------------------------------------
    // --- ParentTableDataFileDb functions to override/re-implement --- 
    // ----------------------------------------------------------------

	public function createNewChild($ib, $aBaseData, $item_base_id )
	{
		if ( '' == $item_base_id ) {
			printf("ERROR: createNewChild. Empty item_base_id\n");
			return;
		}
		$ib->createNew( $aBaseData, $item_base_id, null );
		$item_base_name = $ib->m_aAllData['base_data']['item_base_name'];
		$item_type = $ib->m_aAllData['base_data']['item_type'];
		$item_year = (int)$ib->m_aAllData['base_data']['item_year'];
		$this->m_aAllData['children'][$item_base_id] = array( 
            'item_base_name'=> $item_base_name
            , 'item_type'=> $item_type
            , 'item_year'=> $item_year
            , 'item_base_soundex' => calcSoundex( $item_base_name )
		);
	}

	protected function onChildChanged($ib)
	{
// // 		printf("onChildChanged\n");
		$item_base_id = $ib->m_aAllData['base_data']['item_base_id'];
		$item_year = (int)$ib->m_aAllData['base_data']['item_year'];
		$this->m_aAllData['children'][$item_base_id]['item_year'] = $item_year;
		$this->updatePricesCacheOfItemBase($ib);
		$this->updatePricesCache();
	}

    protected function onChildErase	( $childElem ) 	
    { 
		$item_base_id = $childElem->id();
		$this->updatePricesCache();
	}
	
	// -------------------------------
    // --- Create/update functions --- 
    // -------------------------------
    /** 
    \param $parentTableDataFileDb Should always be null for Artist create. */
    public function createNew ( $aBaseData, $artist_id, $parentTableDataFileDb ) 
    {
		$this->createDirectory();
		if ( !isset($artist_id) || '' == $artist_id ) {
			$artist_id = nameToID( $aBaseData['artist_name'] );
		}
		$aBaseData['artist_id'] = $artist_id;
		$this->m_aAllData['base_data'] = array('artist_name' => $aBaseData['artist_name'], 'artist_id' => $artist_id );
		$this->m_aAllData['children'] = array();
		$this->m_aAllData['text'] = array();
    }
    
    /** Unconditionally update the base data.
     */
    public function updateBaseData ( $aBaseData ) 
    {
		// TODO: Figure out which approach is the fastest!
		$this->doUpdateBaseData( $aBaseData, ArtistDataFileDb::$M_aBaseDataFields );
		//$this->m_aAllData['base_data'] = updateAssocData( $this->m_aAllData['base_data'], $aBaseData, ArtistDataFileDb::$M_aBaseDataFields );
    }
	

    /** Update the base data using reliability.
     */
    public function updateBaseDataCheckReliability ( $aBaseData ) 
    {
		// TODO: Figure out which approach is the fastest!
		$this->doUpdateBaseDataCheckReliability( $aBaseData, ArtistDataFileDb::$M_aBaseDataFields, 'artist_reliability' );
// 		$this->m_aAllData['base_data'] = updateAssocDataCheckReliability( $this->m_aAllData['base_data'], $aBaseData, ArtistDataFileDb::$M_aBaseDataFields, 'artist_reliability' );
    }
	
    // -------------------------------------
    // --- Erase/delete/merge functions ----
    // -------------------------------------
// // 
// //     /** . */
// //     public function merge ($fromArtistDataFileDb)
// //     {
// //     }

    


    // -----------------------------------------------------
    // --- Update pre calculated / cached data functions --- 
    // -----------------------------------------------------

    /** Recalculate prices_cache for main artist.json file. 
    \note This function assumes that ALL itim_base json files have been opened for 
    either read or write. If you have not already opened all those files make sure to 
    call openAllChildrenForRead() first.*/
    public function recalculatePricesCache()
    {
		$price_euro_min =  PHP_INT_MAX;
		$price_euro_max =  PHP_INT_MIN; // Defined in airplay_globals.php
		
		$aArtistPricesCache = array();
		foreach ( $this->m_openChilds as $item_base_id => $ib ) {
			$aItemPricesCache = $ib->getPriceDataForArtist($this->m_currencyConvert);
			if ( empty($aItemPricesCache) ) continue;
			$this->m_aAllData['children'][$item_base_id]['prices_cache'] = $aItemPricesCache;
			
			foreach( $aItemPricesCache as $key => $value ) {
				if ( 'price_euro_min' == $key ) {
					if ( $value > 0 && $value < $price_euro_min ) {
						$price_euro_min = $value;
						$aArtistPricesCache['price_min_item_base_id'] = $item_base_id;
					}
				}
				else if ( 'price_euro_max' == $key ) {
					if ( $value > 0 && $value > $price_euro_max ) {
						$price_euro_max = $value;
						$aArtistPricesCache['price_max_item_base_id'] = $item_base_id;
					}
				}
				else {
					$aArtistPricesCache[$key] = (int)$aArtistPricesCache[$key] + (int)$value;
				}
			}
		}
		$aArtistPricesCache['price_euro_min'] = $price_euro_min;
		$aArtistPricesCache['price_euro_max'] = $price_euro_max;
		$this->m_aAllData['base_data']['prices_cache'] = $aArtistPricesCache;
    }
        
    /** Update prices_cache for main artist.json file. 
		It updates the summary 'prices_cache' in the base_data section in the top 
		of the artist.json file.
		Use this function after updating, adding or deleting an item_base to ensure the the 
		summary section for all the prices for the artist are correct
    \note This function only use the main artist.json file. */
    public function updatePricesCache()
    {
		$price_euro_min =  PHP_INT_MAX;
		$price_euro_max =  PHP_INT_MIN; // Defined in airplay_globals.php
		
		$aArtistPricesCache = array();
		foreach ( $this->m_aAllData['children'] as $item_base_id => $aChild ) {
		
			$aItemPricesCache = $aChild['prices_cache'];
			if ( '' == $aItemPricesCache ) continue;
			
			foreach( $aItemPricesCache as $key => $value ) {
				if ( 'price_euro_min' == $key ) {
					if ( $value > 0 && $value < $price_euro_min ) {
						$price_euro_min = $value;
						$aArtistPricesCache['price_min_item_base_id'] = $item_base_id;
					}
				}
				else if ( 'price_euro_max' == $key ) {
					if ( $value > 0 && $value > $price_euro_max ) {
						$price_euro_max = $value;
						$aArtistPricesCache['price_max_item_base_id'] = $item_base_id;
					}
				}
				else {
					$aArtistPricesCache[$key] = (int)$aArtistPricesCache[$key] + (int)$value;
				}
			}
		}
		$aArtistPricesCache['price_euro_min'] = $price_euro_min;
		$aArtistPricesCache['price_euro_max'] = $price_euro_max;
		$this->m_aAllData['base_data']['prices_cache'] = $aArtistPricesCache;
    }
    

    /** Update prices_cache for in main artist.json file for one ItemBase. 
		Use this function after updating, item_base to ensure the price cache for that item_base is 
		updated . Ie. the ['children'][item_base_id]['prices_cache'] for one item_base in artist.json not 
		the same as the above function updatePricesCache().*/
    public function updatePricesCacheOfItemBase($ib)
    {
		$item_base_id = $ib->id();
		$aItemPricesCache = $ib->getPriceDataForArtist($this->m_currencyConvert);
		if ( empty($aItemPricesCache) ) return;
		$this->m_aAllData['children'][$item_base_id]['prices_cache'] = $aItemPricesCache;
    }
    
    
    // ----------------------------
    // --- Fuzzy find functions ---
	// ----------------------------
	/**
	*/
	function fuzzyFindIdFromSoundex( $nameToFind, $itemTypeToFind, $minimumSimilarityFactor )
	{
		$bestMatchSimilarityFactor;
		$defaultID;
		$id = $this->findBestMatchItemFromSoundex( $nameToFind, $itemTypeToFind, $defaultID, $bestMatchSimilarityFactor );
// 		printf("MatchFac: $bestMatchSimilarityFactor for ($nameToFind, $itemTypeToFind): '$id'\n");

		if ( $bestMatchSimilarityFactor < $minimumSimilarityFactor ) return $defaultID;
		return $id;
	}

	/**
	XXX TODO: Fix doc
	\param 
	*/
	function findBestMatchItemFromSoundex( $nameToFind, $itemTypeToFind, &$defaultID, &$bestMatchSimilarityFactor )
	{
		
		$soundexToFind      = calcSoundex($nameToFind);
		$iLenSoundexToFind  = strlen($soundexToFind);
		if ( $iLenSoundexToFind < 1 )   return 0;  
		
		$bestMatchFac = 1.0; 
		$bestID = '';
		$defaultID = itemBaseNameToID($nameToFind, $itemTypeToFind);
		foreach ( $this->m_aAllData['children'] as $id => $aData ) {
			if ( $itemTypeToFind != $aData['item_type'] ) continue;
			
			// Check for exact ID match
			if ( $defaultID == $id ) {
				$bestMatchFac = 0;
				$bestID = $id;
				break;
			}
 			$levDistSoundex = levenshtein ( $soundexToFind , $aData['item_base_soundex'] );
 			$fac  = $levDistSoundex   / $iLenSoundexToFind;
// 			printf("snd find($fac): id: $id, defaultID: $defaultID   ( $levDistSoundex / $iLenSoundexToFind)\n");
				
			if ( $fac < $bestMatchFac ) {
				$bestMatchFac = $fac;
				$bestID = $id;
			}
		}
		$bestMatchSimilarityFactor = 1.0 - $bestMatchFac;
		return $bestID;
	}
	
    
    // --------------------------------------
    // --- Artist text/article functions ----
    // --------------------------------------

    /**  Update/create article/text for a given language_code
    */
    public function updateText ( $language_code, $artist_article, $artist_text_reliability )
    {
		if ( '' == $language_code || '' == $artist_article ) return;
		$reliabilityOld = (int)$this->m_aAllData['text'][$language_code]['artist_text_reliability'];
		$reliabilityNew = (int)$artist_text_reliability;
		$bNewDataBetter = $reliabilityNew > $reliabilityOld;

		if ( $bNewDataBetter || '' == $this->m_aAllData['text'][$language_code]['artist_article'] ) {
			$this->m_aAllData['text'][$language_code]['artist_article'] = $artist_article;
			$this->m_aAllData['text'][$language_code]['artist_text_reliability'] = $artist_text_reliability;
		}
	}
    

    // ---------------------------------
    // --- Alias handling functions ----
    // ---------------------------------
     /**  Check if a given alias exists for artist. 
    \return ID of alias or zero if not found. */
    public function aliasExists ($artist_id, $alias_name)
    {
    }

    
    /**  Create new alias for artist. 
    \return ID of new alias. */
    public function aliasCreateNew ($artist_id, $alias_name)
    {
    }

    /**  Delete alias for artist. 
    \return DB execute result. */ 
    public function aliasDeleteByName ($artist_id, $alias_name)
    {
    }

    /**  Delete alias for artist. 
    \return ID of new alias. */
    public function aliasDelete ($artist_alias_id)
    {
    }
    
    // --------------------------
    // --- PRIVATE: Functions --- 
    // --------------------------
    
    // ---------------------
    // --- PRIVATE: Data --- 
    // ---------------------
    private				$m_currencyConvert;
    
    private static    	$M_aBaseDataFields = array(  	'artist_url', 'genre_id', 'artist_subgenres', 'country_id', 'artist_reliability'
													  , 'gender', 'artist_real_name', 'url_artist_official', 'url_fanpage', 'url_wikipedia'
													  , 'url_allmusic', 'url_musicbrainz', 'url_discogs', 'artist_type', 'country_id'
													  , 'year_born', 'year_died', 'year_start', 'year_end', 'google_score'
													  , 'bing_score', 'bing_score' ); 

    
}

?>