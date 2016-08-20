<?php

require_once ("db_helpers.php");

class ArtistDataMySql
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $dbPDO = null )
    {
        global $g_MySqlPDO;
        $this->m_dbPDO = $dbPDO;
        if ( $dbPDO == null ) $this->m_dbPDO = $g_MySqlPDO;      
    }

    // ----------------------------------
    // --- Artist ID lookup functions --- 
    // ----------------------------------
// //     /** Return array of arrays of (artist_id, artist_name) */
// //     public function lookUpID ($artist_name)
// //     {
// //         $query = "SELECT artist_id, artist_name FROM artist WHERE artist_name = '%s'";
// //         $q = sprintf( $query,  mysql_real_escape_string($artist_name)  );
// //         return doQueryAssoc($q); 
// //     }
// //     
// //     /** Return one artist ID if found one and only one matching. Zero if not found any or more than one found. */
// //     public function lookUpIDExact ($artist_name)
// //     {
// //         $a = $this->lookUpArtistID($artist_name);
// //         if ( count($a) != 1 )   return 0;
// //         else                    return $a[0]['artist_id'];
// //     }

    
    /** Lookup artist ID from artist "base/real/official" name.
    \return one artist ID if found one and only one matching. Zero if not found any and -n if more than one (n) found.  */
    public function nameToID ($artist_name)
    {
        $q = "SELECT artist_id FROM artist WHERE artist_name = ?";
        return pdoLookupSingleIntQuery($this->m_dbPDO, $q, array($artist_name) );
    }
    
    /** Lookup artist ID from artist alias name.
    \return one artist ID if found one and only one matching. Zero if not found any and -n if more than one (n) found.  */
    public function aliasToID ($artist_alias)
    {
        $q = "SELECT artist_id FROM artist_synonym WHERE artist_synonym_name = ?";
        return pdoLookupSingleIntQuery($this->m_dbPDO, $q, array($artist_alias) );
    }
    
    /** Lookup artist ID from  artist name or alias.
    \return artist_id if found one and only one matching. Zero if not found any and -n if more than one (n) found.  */
    public function lookupID ($artist_name)
    {
        $id = $this->nameToID ($artist_name);
        if ( $id <= 0 )  $id = $this->aliasToID ($artist_name);
        return $id;
   }

// //      /** Lookup artist ID from  artist name or alias.
// //     \return One array(artist_id, is_alias) if found one and only one matching. Zero if not found any and -n if more than one (n) found.  */
// //     public function lookupID ($artist_name)
// //     {
// //         $id = $this->nameToID ($artist_name);
// //         if ( $id > 0 )  return array( $id, 0 );
// //         $id = $this->aliasToID ($artist_name);
// //         if ( $id > 0 )  return array( $id, 1 );
// //         else            return array( $id, 0 );
// //     }
   
    // --------------------------
    // --- Get data functions --- 
    // --------------------------
    /** Get artist base data,. Info like name, genre, wiki info etc. */
    public function getBaseData ($artist_id)
    {
        $q = "SELECT * FROM artist, info_artist WHERE artist.artist_id = $artist_id AND info_artist.artist_id = $artist_id";
        $aData = pdoQueryAssocFirstRow($this->m_dbPDO, $q); 
        
        // In case there are no entry in info_artist we try again ...
        if ( count($aData) == 0 ) {
            $q = "SELECT * FROM artist WHERE artist.artist_id = $artist_id";
            $aData = pdoQueryAssocFirstRow($this->m_dbPDO, $q); 
        }
        return $aData;
    }
    
    /** Get artist official name from ID. 
    \return Official AP name. */
    public function IDToName ($artist_id)
    {
        $aData = $this->getBaseData ($artist_id);
        return $aData['artist_name'];
    }
    
    
    /** Get artist base item IDs. */
    public function getBaseItemIDs ($artist_id, $item_type)
    {
        if ( $item_type == -1 ) $q = "SELECT item_base_id FROM item_base WHERE artist_id = $artist_id";
        else                    $q = "SELECT item_base_id FROM item_base WHERE artist_id = $artist_id AND item_type = $item_type";
        return pdoQueryAllRowsFirstElem($this->m_dbPDO, $q); 
    }

    /** Get artist base album IDs. */
    public function getBaseAlbumIDs ($artist_id)
    {
        return $this->getBaseItemIDs($artist_id, 0 );
    }
    
    /** Get artist base song IDs. */
    public function getBaseSongIDs ($artist_id)
    {
        return $this->getBaseItemIDs($artist_id, 1 );
    }

    // ----------------------------------------------------------------------------------------
    // --- Functions dealing with the old DB structure (should be removed after conversion) ---
    // ----------------------------------------------------------------------------------------
    /** OLD Get artist simple album IDs. */
    public function old_getSimpleAlbumIDs ($artist_id)
    {
        $q = "SELECT album_simple_id FROM album_simple WHERE artist_id = $artist_id";
        $a = pdoQueryAllRowsFirstElem($this->m_dbPDO, $q); 
        return $a;
    }
    
    /**  OLD Get artist simple song IDs. */
    public function old_getSimpleSongIDs ($artist_id)
    {
        $q = "SELECT song_simple_id FROM song_simple WHERE artist_id = $artist_id";
        $a = pdoQueryAllRowsFirstElem($this->m_dbPDO, $q); 
        return $a;
    }
    // ----------------------------------------------------------------------------------------
    
    
    /** Get all data needed to display an artist page */
    public function getPageData ($artist_id)
    {
        // TODO: Implement this!
//         $q = "SELECT * from artist, info_artist WHERE artist.artist_id = $artist_id AND info_artist.artist_id = $artist_id";
//         $a = doQueryAssoc($q); 
//         
//         return $a;
    }
    
    
    // --------------------------
    // --- Set data functions --- 
    // --------------------------
    /**  Set base data of artist. Creates new artist if name not found. */
    public function setBaseData ($aData)
    {

    }
        
        
    /**  Create new artist. 
    \return ID of new artist. */
    public function createNew ($artist_name)
    {
        $stmt = $this->m_dbPDO->prepare("INSERT INTO artist (artist_name) VALUES (:artist_name)" );
        $stmt->execute(array(':artist_name' => $artist_name));
        //TODO: fix me    
        
        $artist_id = (int)$this->m_dbPDO->lastInsertId();
        
        // Create entry in info_artist also 
        $stmt = $this->m_dbPDO->prepare("INSERT INTO artist (artist_name) VALUES (:artist_name)" );
        $stmt->execute(array(':artist_name' => $artist_name));
        return $artist_id;
    }
    
    /**  Update base data of existing artist. */
    public function updateBaseData ($aData)
    {
        static $aArtistTblFields     = array( 'artist_name', 'artist_url', 'genre_id', 'artist_genre_id', 'subgenre_id', 'country_id' );
        static $aInfoArtistTblFields = array( 'artist_id', 'gender', 'artist_real_name', 'url_artist_official', 'url_fanpage', 'url_wikipedia', 'url_allmusic', 'url_musicbrainz', 'url_discogs', 'artist_type', 'country_id', 'year_born', 'year_died', 'year_start', 'year_end', 'google_score', 'bing_score' );

       
        $result = 0;
        $artist_id = $aData['artist_id'];

        $aUpd = pdoGetUpdate ($aData, $aArtistTblFields );
        if ($aUpd[0] != "" ) {
            $q = 'UPDATE artist SET ' . $aUpd[0] . ' WHERE artist_id = ?';
            $aUpd[1][] = $artist_id;
            $stmt = $this->m_dbPDO->prepare($q);
            $stmt->execute($aUpd[1]);
            $result += $stmt->rowCount();
        }

        $aUpd = pdoGetUpdate ($aData, $aInfoArtistTblFields  );
        if ($aUpd[0] != "" ) {
            $q = 'UPDATE info_artist SET ' . $aUpd[0] . ' WHERE artist_id = ?';
            $aUpd[1][] = $artist_id;
            $stmt = $this->m_dbPDO->prepare($q);
            $stmt->execute($aUpd[1]);
            $result += $stmt->rowCount();
        }
        return $result;
    }
    

    // ---------------------------------
    // --- Alias handling functions ----
    // ---------------------------------
     /**  Check if a given alias exists for artist. 
    \return ID of alias or zero if not found. */
    public function aliasExists ($artist_id, $alias_name)
    {
        $q = "SELECT artist_synonym_id FROM artist_synonym WHERE artist_id = ? AND artist_synonym_name = ?";
        return pdoLookupSingleIntQuery($this->m_dbPDO, $q, array($artist_id, $alias_name) );
    }

    
    /**  Create new alias for artist. 
    \return ID of new alias. */
    public function aliasCreateNew ($artist_id, $alias_name)
    {
        $stmt = $this->m_dbPDO->prepare("INSERT INTO artist_synonym (artist_id, artist_synonym_name) VALUES (?,?)" );
        $stmt->execute( array($artist_id, $alias_name) );
        return (int)$this->m_dbPDO->lastInsertId();
    }

    /**  Delete alias for artist. 
    \return DB execute result. */ 
    public function aliasDeleteByName ($artist_id, $alias_name)
    {
        $stmt = $this->m_dbPDO->prepare("DELETE FROM artist_synonym WHERE artist_id = ? AND artist_synonym_name = ?" );
        return $stmt->execute( array($artist_id, $alias_name) );
    }

    /**  Delete alias for artist. 
    \return ID of new alias. */
    public function aliasDelete ($artist_alias_id)
    {
        $stmt = $this->m_dbPDO->prepare("DELETE FROM artist_synonym WHERE artist_synonym_id = ?" );
        return $stmt->execute( array($artist_alias_id) );
    }
    
    // --------------------------
    // --- PRIVATE: Functions --- 
    // --------------------------
    
    // ---------------------
    // --- PRIVATE: Data --- 
    // ---------------------
    private         $m_dbPDO = null;
    
}


// ########################################
// ########################################
////        printf("query: %s\n", $q);

?>
