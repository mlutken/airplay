<?php

require_once ('db_manip/MusicDatabaseFactory.php');


class MusicDatabaseManip
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public  function    __construct( $dbPDO = null, $redis = null )
    {
        $fac = new MusicDatabaseFactory($dbPDO, $redis);
        $this->m_dbItemBaseCorrection   = $fac->createDbInterface("ItemBaseCorrectionData");
        $this->m_dbRecordStore          = $fac->createDbInterface("RecordStoreData");
        $this->m_MediaFormatLookup      = $fac->createDbInterface("MediaFormatLookup");
        $this->m_MediaTypeLookup        = $fac->createDbInterface("MediaTypeLookup");
        $this->m_dbArtistData           = $fac->createDbInterface("ArtistData");
        $this->m_dbItemBaseData         = $fac->createDbInterface("ItemBaseData");
        $this->m_dbItemBaseReviewData   = $fac->createDbInterface("ItemBaseReviewData");
        $this->m_dbGenreLookup          = $fac->createDbInterface("GenreLookup");
        $this->m_dbItemPriceData        = $fac->createDbInterface("ItemPriceData");
        $this->m_dbCurrencyData         = $fac->createDbInterface("CurrencyData");

    }

    // ------------------------
    // --- Lookup functions --- 
    // ------------------------
    
    public function lookupSimilarNames( $name )
    {
        $aRes1 = $this->m_dbArtistData->lookupSimilarNames( $name );
        $aRes2 = $this->m_dbItemBaseData->lookupSimilarNames( $name );
        return $aRes1 + $aRes2;
    }

    public function lookupSimilarNamesOut( $name, &$aArtists, &$aItemBases )
    {
        $aArtists = $this->m_dbArtistData->lookupSimilarNames( $name );
        $aItemBases = $this->m_dbItemBaseData->lookupSimilarNames( $name );
    }
    
    
    // -----------------------
    // --- Merge functions --- 
    // -----------------------

    public function mergeArtist ($into_artist_id, $from_artist_id)
    {
        if ( $into_artist_id == $from_artist_id ) return "Error: Merging artist with itself. into_artist_id == from_artist_id == $into_artist_id";
        if ( '' == $into_artist_id || 0 == $into_artist_id ) return "Error: Merging artist. into_artist_id empty\n";
        if ( '' == $from_artist_id || 0 == $from_artist_id ) return "Error: Merging artist. from_artist_id empty\n";
        $from_artist_name = $this->m_dbArtistData->IDToName($from_artist_id);
        $this->m_dbArtistData->merge($into_artist_id, $from_artist_id);
        
        $aFromBaseItemIDs = $this->m_dbItemBaseData->getItemBaseIDs ($from_artist_id, 0);
        foreach( $aFromBaseItemIDs as $from_item_base_id ) {
            $this->mergeItemBaseToArtist( $into_artist_id, $from_item_base_id );
        }
        
        $this->m_dbItemBaseCorrection->mergeArtist($into_artist_id, $from_artist_id);

        if ( !$this->m_dbArtistData->aliasExists($into_artist_id, $from_artist_name) ) {
            if ( '' != $from_artist_name ) {
                $this->m_dbArtistData->aliasCreateNew ($into_artist_id, $from_artist_name);
            }
        }
        
        $this->eraseArtist($from_artist_id);
        return "OK";
    }

    public function mergeItemBase ($into_item_base_id, $from_item_base_id)
    {
        if ( $into_item_base_id == $from_item_base_id ) return "Error: Merging itemBase with itself. into_item_base_id == from_item_base_id == $into_item_base_id";
        $aFromItemBaseData = $this->m_dbItemBaseData->getBaseData($from_item_base_id);
        $aIntoItemBaseData = $this->m_dbItemBaseData->getBaseData($into_item_base_id);
///        if ( $aFromItemBaseData['artist_id'] != $aIntoItemBaseData['artist_id'] )   return "Error: Merge ItemBase must have same artist_id!";
        
        
        $this->m_dbItemBaseData->merge($into_item_base_id, $from_item_base_id);
        
        // --- Merge prices for the newly merged ItemBase ---
        $aFromItemPriceIDs   = $this->m_dbItemPriceData->getItemPriceIDs( $from_item_base_id );
        foreach ( $aFromItemPriceIDs as $from_item_price_id ) {
            $this->mergeItemPriceToItemBase( $aIntoItemBaseData['artist_id'], $into_item_base_id, $from_item_price_id );
        }
        

        if ( !$this->m_dbItemBaseCorrection->correctionExists ($aIntoItemBaseData['artist_id'], $aFromItemBaseData['item_base_name'], $aIntoItemBaseData['item_base_name'] ) ) {
            $this->m_dbItemBaseCorrection->createNew ($aIntoItemBaseData['artist_id'], $aFromItemBaseData['item_base_name'], $aIntoItemBaseData['item_base_name'] );
        }
        
        $this->eraseItemBase($from_item_base_id);
        return "OK";
    }
    

    
    /** Merge item_base to a new artist "owner". If the name of the \a $from_item_base_id can 
    be found int the \a $into_artist_id's list of item_base names then we do a (BaseData) merge only. 
    If the name is not found we simply move the item to the new artist "owner". */
    public function mergeItemBaseToArtist ( $into_artist_id, $from_item_base_id )
    {
        $aFromBaseItemData  = $this->m_dbItemBaseData->getBaseData ($from_item_base_id);
        
        $aFromBaseItemData['artist_id'] = $into_artist_id;
        $item_base_id = $this->m_dbItemBaseData->toID($aFromBaseItemData);
        if ( 0 == $item_base_id) {
            $this->m_dbItemBaseData->moveToArtist( $into_artist_id, $from_item_base_id);
			
			// --- Move prices for the newly moved ItemBase ---
			$aAllItemPriceIDs   = $this->m_dbItemPriceData->getItemPriceIDs( $from_item_base_id );
			foreach ( $aAllItemPriceIDs as $from_price_id ) {
				$this->m_dbItemPriceData->moveToArtist( $into_artist_id, $from_price_id );
			}
        }
        else {
            $this->m_dbItemBaseData->merge( $item_base_id, $from_item_base_id);
            
			// --- Merge prices for the newly merged ItemBase ---
			$aAllItemPriceIDs   = $this->m_dbItemPriceData->getItemPriceIDs( $from_item_base_id );
			foreach ( $aAllItemPriceIDs as $from_price_id ) {
				$this->mergeItemPriceToItemBase( $into_artist_id, $item_base_id, $from_price_id );
			}
        }
        
//         // --- Merge prices for the newly merged ItemBase ---
//         $into_item_base_id  = $from_item_base_id; // Just to emphasize that now this item_base is the new merge destination (for it's prices)
//         $aAllItemPriceIDs   = $this->m_dbItemPriceData->getItemPriceIDs( $into_item_base_id );
//         foreach ( $aAllItemPriceIDs as $from_item_price_id ) {
//             $this->mergeItemPriceToItemBase( $into_artist_id, $into_item_base_id, $from_item_price_id );
//         }
    }

 
    
    /** Merge item_price to a new item_base "owner". If the name of the \a $from_item_price_id can 
    be found int the \a $into_item_base_id's list of item_price names then we do a delete only. 
    If the name is not found we simply move the item to the new item_base "owner". */
    public function mergeItemPriceToItemBase ( $into_artist_id, $into_item_base_id, $from_item_price_id )
    {
        $aIntoItemBaseData      = $this->m_dbItemBaseData->getBaseData($into_item_base_id);
        
        if ( '' == $into_artist_id || 0 == $into_artist_id ) {
            $into_artist_id = $aIntoItemBaseData['artist_id'];
        }
        $aPriceData = $this->m_dbItemPriceData->getBaseData($from_item_price_id);
        
        $aPriceData['artist_id']    = $into_artist_id;
        $aPriceData['item_base_id'] = $into_item_base_id; 
        
        $existing_price_id = $this->m_dbItemPriceData->toID($aPriceData);
        if ( ($existing_price_id != 0) && ($existing_price_id != $from_item_price_id) ) {
			$this->m_dbItemPriceData->erase($from_item_price_id);
        }
        else {
			$this->m_dbItemPriceData->moveToItemBase( $into_artist_id, $into_item_base_id, $from_item_price_id );
		}
    }
    
    
    // ------------------------
    // --- Erase functions --- 
    // ------------------------

    function eraseArtist( $artist_id )
    {
        $this->m_dbArtistData->erase($artist_id);
        $aAllItemBaseCorrectionIDs = $this->m_dbItemBaseCorrection->getCorrectionIDsForArtist($artist_id);
        foreach ( $aAllItemBaseCorrectionIDs as $id ) {
            $this->m_dbItemBaseCorrection->erase($id);
        }
        
        $aAllItemBaseIDs = $this->m_dbItemBaseData->getItemBaseIDs( $artist_id, 0 );
        foreach ( $aAllItemBaseIDs as $item_base_id ) {
            $this->eraseItemBaseFromArtist( $artist_id, $item_base_id );
        }
    }

   function eraseItemBase ( $item_base_id )
   {
        $aItemData = $this->m_dbItemBaseData->getBaseData($item_base_id);
        $this->eraseItemBaseFromArtist( $aItemData['artist_id'], $item_base_id );
    }
    
   function eraseItemBaseFromArtist( $artist_id, $item_base_id )
   {
        $aItemData = $this->m_dbItemBaseData->getBaseData($item_base_id);
        if ( $artist_id != $aItemData['artist_id'] )   return;
        
        $aAllItemPrices = $this->m_dbItemPriceData->getItemPrices( $item_base_id );
        foreach ( $aAllItemPrices as $aPriceData ) {
            $item_price_id = $aPriceData['item_price_id'];
            if ( $aPriceData['artist_id'] == $artist_id ) {
                $this->eraseItemPrice($item_price_id);
            }
        }
        $this->m_dbItemBaseData->erase($item_base_id);
    }

    
    function eraseItemPrice( $item_price_id )
    {
        $this->m_dbItemPriceData->erase($item_price_id);
    }
    
    // --------------------------
    // --- PRIVATE: Functions --- 
    // --------------------------
     
    // ---------------------
    // --- PRIVATE: Data --- 
    // ---------------------
    private         $m_dbPDO = null;
    private         $m_redis = null;
    private         $m_dbItemBaseCorrection;
    private         $m_dbRecordStore;
    private         $m_MediaFormatLookup;
    private         $m_MediaTypeLookup;
    private         $m_dbArtistData;
    private         $m_dbItemBaseData;
    private         $m_dbGenreLookup;
    private         $m_dbItemPriceData;
    private         $m_dbCurrencyData;
    
    
}


?>