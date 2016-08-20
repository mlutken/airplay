<?php

require_once ("new_db_inserter/BaseInserterFileDb.php");
require_once ('filedb_api/ArtistDataFileDb.php');



class ItemPriceInserterFileDb extends BaseInserterFileDb
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $fileDbBaseDir, $dbAll, $openParents )
    {
        parent::__construct( $fileDbBaseDir, $dbAll, $openParents );
    }

    public function insertToDB ( $aBaseData )
    {
		$artist_name = $aBaseData['artist_name'];
		if ( '' == $artist_name )	return;
		$artist_name_lower_case 	= mb_strtolower( $artist_name, 'UTF-8' );
		

		// ------------------------------
		// --- Check for artist alias ---
		// ------------------------------
		$bExists = $this->checkArtistAlias( $artist_name, $artist_name_lower_case );


		// --------------------------------------------------------------------------------------------------
		// --- Get artist_id and do modulo check to see if this instance should handle this artist at all ---
		// --------------------------------------------------------------------------------------------------
		$artist_id = nameToIDLowercase($artist_name_lower_case);
		$hash32 = hash32($artist_id);
		
		if ( $hash32 % $this->m_iModuloBase != $this->m_iModuloMatch ) {
// 			printf("Modulo skipping'$artist_name': %d\n", $hash32 % $this->m_iModuloBase );
			return;
		}
		

		// ---------------------------------------------------------
		// --- Set data_record_type specific values in aBaseData ---
		// ---------------------------------------------------------
		$data_record_type = $aBaseData['data_record_type'];
		if ( '' == $data_record_type ) return;
		
		$item_price_name = '';
		$parent_price_name = '';
		switch ( $data_record_type )
		{
			case 'album' :
				$item_price_name 				= $aBaseData['album_name'];
				$aBaseData['item_type']			= 1;
				if ( '' == $aBaseData['item_year'] ) $aBaseData['item_year'] = $aBaseData['album_year'];
				break;
			case 'song' :
				$item_price_name 				= $aBaseData['song_name'];
				$aBaseData['item_type']			= 2;
				if ( '' == $aBaseData['item_year'] ) $aBaseData['item_year'] = $aBaseData['song_year'];
				// --- Parent item ---
				$parent_album_name = $aBaseData['album_name'];
				if ( '' != $parent_album_name ) {
					$parent_album_base_name = cleanItemName( $parent_album_name, $this->m_dbAll->m_dbItemBaseAliasLookup, $artist_name_lower_case );
					$aBaseData['parent_item_name'] 	= $parent_album_base_name;
					$aBaseData['parent_item_id'] 	= itemBaseNameToID( $parent_album_base_name, 1 );
					$aBaseData['parent_item_type'] 	= 1;
					$parent_price_name 				= $parent_album_name;
				}
				break;
			case 'merchandise' :
				$item_price_name 				= $aBaseData['merchandise_name'];
				$aBaseData['item_type']			= 3;
				if ( '' == $aBaseData['item_year'] ) $aBaseData['item_year'] = $aBaseData['merchandise_year'];
				break;
			default:
				logError("(ItemPriceInserterFileDb::insertToDB) Unknown data_record_type: '$data_record_type'");
				return;
				
		}
		
		$aBaseData['item_price_name']	= $item_price_name;
		$aBaseData['item_base_name'] 	= cleanItemName($item_price_name, $this->m_dbAll->m_dbItemBaseAliasLookup, $artist_name_lower_case );
		
		
		// ---------------------
		// --- Artist update ---
		// ---------------------
		$bExists = $this->m_openParents->artistOpenForWrite($artist_id, $hash32);
		if ( !$bExists ) {
//  			printf("CREATE NEW ARTIST: '$artist_name'\n");
 			$this->m_openParents->m_ad->createNew( $aBaseData, $artist_id, null );
			$this->m_openParents->artistAddNewName($artist_name);
		}
		$this->m_openParents->m_ad->updateBaseDataCheckReliability($aBaseData);

		// -----------------------
		// --- ItemBase update ---
		// -----------------------
		$item_base_name = $aBaseData['item_base_name'];
		if ( '' == $item_base_name ){
			return;
		}
		
		$item_base_id = $this->m_openParents->m_ad->fuzzyFindIdFromSoundex ($item_base_name, $aBaseData['item_type'], 0.9 );
		
		
		$ib;
		$bExists = $this->m_openParents->m_ad->openChildForWrite($item_base_id, $ib);
		if ( !$bExists ) {
//    			printf("CREATE NEW ITEM BASE: '$item_base_name', item_base_id: '$item_base_id'\n");
 			$this->m_openParents->m_ad->createNewChild( $ib, $aBaseData, $item_base_id );
			$this->m_openParents->itemBaseAddNewName( $item_base_name, $aBaseData['item_type'] );
		}
		$ib->updateBaseDataCheckReliability($aBaseData);
		
		// ------------------------
		// --- ItemPrice update ---
		// ------------------------
		$media_format_id = $this->m_dbAll->m_MediaFormatLookup->lookupID($aBaseData['media_format_name']);
		$aBaseData['media_format_id'] = $media_format_id;
		$aBaseData['currency_id'] = $aBaseData['currency_name'];
		
		// --- Parent item price ---
		if ( '' != $parent_price_name ) {
			$aBaseData['parent_price_id'] = createItemPriceID( $parent_price_name, $media_format_id, $aBaseData['record_store_name'], $aBaseData['item_used'], 1 );
		}

		
		$ib->updatePrice( $aBaseData, $this->m_dbAll->m_CurrencyConvert, $this->m_tsNow );
		
    }
    
    // ---------------------------------
    // --- PRIVATE: Helper functions --- 
    // ---------------------------------
	private function checkArtistAlias(&$artist_name, &$artist_name_lower_case)
	{
		$artist_id = nameToIDLowercase($artist_name_lower_case);
		if ( $this->m_openParents->m_ad->idExists($artist_id) ) return;
	
		// --- Check existing aliases ---
		$alias_name = $this->m_dbAll->m_dbArtistAliasLookup->aliasNameToArtistName($alias_name_lower_case); 
		if ( '' != $alias_name ) {
			printf("Found alias '$alias_name' for '$artist_name'\n");
			$artist_name = $alias_name;
			$artist_name_lower_case = mb_strtolower( $artist_name, 'UTF-8' );
			return;
		}
	
		return;
		// Try reversing artist name and see if we can find
	
		$bExists = $this->m_openParents->artistOpenForWrite($artist_id, $hash32);
		if ( $bExists ) return true;
		
        $newAlias = '';
        $artistNameLowercase = '';
        $artistID = '';
        $h32 = 0;
        
//		printf("TRY ALIAS reverseArtistNameWithComma : '$artist_name'\n");
		
		// Try reversed lookup for two word names with comma between the words (ex. 'Turner, Tina')
		$artistNameReversed = reverseArtistNameWithComma($artist_name);
		if ( $artistNameReversed != $artist_name ) {
			
			$artistNameLowercase = mb_strtolower( $artistNameReversed, 'UTF-8' );
			$artistID = nameToIDLowercase($artistNameLowercase);
			$h32 = hash32($artistID);

			$bExists = $this->m_openParents->artistOpenForWrite($artistID, $h32);
			if ( $bExists ) {
				$newAlias = $artist_name;
			}
		}
  			

		if ( !$bExists ) {

//			printf("TRY ALIAS reverseArtistName : '$artist_name'\n");
			
			// Try reversed lookup for two word names without comma (ex. 'Turner Tina')
			$artistNameReversed = reverseArtistName($artist_name);
			if ( $artistNameReversed != $artist_name ) {
				
				$artistNameLowercase 	= mb_strtolower( $artistNameReversed, 'UTF-8' );
				$artistID = nameToIDLowercase($artistNameLowercase);
				$h32 = hash32($artistID);

				$bExists = $this->m_openParents->artistOpenForWrite($artistID, $h32);
				if ( $bExists ) {
					$newAlias = $artist_name;
				}
			}
  			
		}
		
		// Set new values for artist_name, artist_name_lower_case, artist_id, hash32
		if ( $bExists ) {
			printf("Exists: '$newAlias' for '$artist_name'\n");
		
			$artist_name = $artistNameReversed;
			$artist_name_lower_case = $artistNameLowercase;
			$artist_id = $artistID;
			$hash32 = $h32;

		}
		if ( '' != $newAlias ) {
			printf("createNewArtistAlias: '$newAlias' for '$artist_name'\n");
		}
	
		
		return $bExists;

	}
    
    // ---------------------
    // --- PRIVATE: Data --- 
    // ---------------------
    
}


?>