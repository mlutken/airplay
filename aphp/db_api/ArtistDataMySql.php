<?php

require_once ("db_api/SimpleTableDataMySql.php");


// 'artist_genre_id'

class ArtistDataMySql extends SimpleTableDataMySql
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $dbPDO = null )
    {
        parent::__construct( 
        'artist'
        , array(  'artist_name', 'artist_url', 'genre_id', 'subgenre_id', 'country_id', 'artist_reliability', 'artist_soundex', 'gender', 'artist_real_name', 'url_artist_official', 'url_fanpage', 'url_wikipedia', 'url_allmusic', 'url_musicbrainz', 'url_discogs', 'artist_type', 'country_id', 'year_born', 'year_died', 'year_start', 'year_end', 'google_score', 'bing_score', 'info_artist_reliability' ) 
        , $dbPDO );
    }

    // -------------------------------------
    // --- Erase/delete/merge functions ----
    // -------------------------------------
    /** Completely erase an entry. */
    public function erase ($artist_id)
    {
        $aArgs = array($artist_id);
        $stmt = $this->m_dbPDO->prepare( 'DELETE FROM artist WHERE artist_id = ?' );
        $stmt->execute( $aArgs );
    
        $stmt = $this->m_dbPDO->prepare( 'DELETE FROM info_artist WHERE artist_id = ?' );
        $stmt->execute( $aArgs );

        $stmt = $this->m_dbPDO->prepare( 'DELETE FROM  artist_synonym WHERE artist_id = ?' );
        $stmt->execute( $aArgs );
        
        $stmt = $this->m_dbPDO->prepare('DELETE FROM artist_text WHERE artist_id = ?' );
        $stmt->execute( $aArgs );
    }

    /** . */
    public function merge ($into_artist_id, $from_artist_id)
    {
        if ( $into_artist_id == $from_artist_id ) return;
        $aBaseDataInto = $this->getBaseData($into_artist_id);
        $aBaseDataFrom = $this->getBaseData($from_artist_id);
        $aBaseDataFrom['artist_id']     = $into_artist_id;
        $aBaseDataFrom['artist_name']   = $this->IDToName($into_artist_id);
        
        // We don't want to overwrite base data unless into_artist has empty fields
        // The text we don't merge at all!
        $aBaseDataFrom['artist_reliability']        = (int)$aBaseDataInto['artist_reliability'] - 1;
        $aBaseDataFrom['info_artist_reliability']   = (int)$aBaseDataInto['info_artist_reliability'] -1;
        $this->updateBaseDataCheckOld($aBaseDataFrom);
        
        // Merge aliases
        $stmt = $this->m_dbPDO->prepare( 'UPDATE artist_synonym SET artist_id=? WHERE artist_id = ?' );
        $stmt->execute( array($into_artist_id, $from_artist_id ) );
    }
    
    // ----------------------------------
    // --- Artist ID lookup functions --- 
    // ----------------------------------
   
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
        if ( $id == 0   )  $id = $this->aliasToID ($artist_name);
        return $id;
    }

    // ------------------------------
    // --- Names lookup functions --- 
    // ------------------------------
    public function lookupSimilarNames ( $name )
    {
        $q = "SELECT artist_id, artist_name FROM artist WHERE artist_name LIKE ?";
        return pdoQueryAssocRows( $this->m_dbPDO, $q, array("{$name}%") );
    }

    public function lookupSimilarBaseData ( $name )
    {
        $q = "SELECT * FROM artist INNER JOIN info_artist ON info_artist.artist_id=artist.artist_id WHERE artist_name LIKE ?";
        return pdoQueryAssocRows( $this->m_dbPDO, $q, array("{$name}%") );
    }

    // --------------------------
    // --- Get data functions --- 
    // --------------------------

    /** Get aray of auto completed artists names given the search string */
    function autoCompleteNames($sSearchFor)
    {
    $q =
<<<TEXT
SELECT DISTINCT Res.artist_name
FROM (
SELECT artist_name, item_price_count
FROM artist
WHERE artist_name LIKE ?
UNION SELECT artist_synonym_name, item_price_count
FROM artist_synonym
INNER JOIN artist ON artist.artist_id = artist_synonym.artist_id
WHERE artist_synonym_name = ?
) AS Res
ORDER BY Res.item_price_count DESC
LIMIT 0 , 10
TEXT;
        
        return pdoQueryAllRowsFirstElem( $this->m_dbPDO, $q, array("{$sSearchFor}%", $sSearchFor) );
    }
    
	
	/*
		Get number of items pr item_type for an artist
	*/
	function getItemTypeCountForArtist ($artist_id) {
		$q = "SELECT COUNT(*) AS item_type_count, item_type FROM item_base WHERE artist_id = ? GROUP BY item_type ORDER BY item_type ASC";
		$a = array($artist_id);
        return pdoQueryAssocRows($this->m_dbPDO, $q, $a );
	}
	
	/*
		Get number of items pr item_type for an artist
	*/
	function getPriceCountPrMediaFormatForArtist ($artist_id, $item_type) {
		$q = "SELECT COUNT(item_price.media_format_id) As media_format_count, media_format_name, media_format.media_format_id
		FROM item_price INNER JOIN media_format ON media_format.media_format_id = item_price.media_format_id
		INNER JOIN item_base ON item_base.item_base_id = item_price.item_base_id
		WHERE item_base.item_type = ? AND item_base.artist_id = ? GROUP BY item_price.media_format_id";
		$a = array($item_type, $artist_id);
        return pdoQueryAssocRows($this->m_dbPDO, $q, $a );
	}
	
	
    /** Get artist base data,. Info like name, genre, wiki info etc. */
    public function getBaseData ($artist_id)
    {
        $q = "SELECT * FROM artist, info_artist WHERE artist.artist_id = ? AND info_artist.artist_id = ?";
        $aData = pdoQueryAssocFirstRow($this->m_dbPDO, $q, array($artist_id, $artist_id) ); 
        
        // In case there are no entry in info_artist we try again ...
        if ( count($aData) == 0 ) {
            $q = "SELECT * FROM artist WHERE artist.artist_id = ?";
            $aData = pdoQueryAssocFirstRow($this->m_dbPDO, $q, array($artist_id)); 
        }
        return $aData;
    }

    /** Get all base data rows from table, obeying the limits given. */
    public function getBaseDataRows ( $start, $count )
    {
        $limit = "LIMIT $start, $count";
        if ( $count == 0 ) $limit = '';
        $q = "SELECT * FROM artist INNER JOIN info_artist ON info_artist.artist_id=artist.artist_id ${limit}";
        return pdoQueryAssocRows($this->m_dbPDO, $q); 
    }

    public function getAutoMergeData($artist_id, $item_type)
    {
		$q = "SELECT COUNT(*) AS nCount, artist.artist_name, item_base.artist_id, item_base.item_base_id, item_base.item_base_name, item_base.item_type, item_base.item_master
		FROM item_base
		LEFT JOIN item_price ON item_base.item_base_id = item_price.item_base_id
		LEFT JOIN artist ON artist.artist_id = item_base.artist_id
		WHERE item_base.artist_id = ? AND item_base.item_type = ?
		GROUP BY item_base.item_base_id 
		ORDER BY nCount DESC";
		$a = array($artist_id, $item_type);
        return pdoQueryAssocRows($this->m_dbPDO, $q, $a );
    }

    public function getAllBaseItemsWithNumPrices($artist_id, $item_type)
    {
        $q = "SELECT COUNT(*) AS prices_count, artist.artist_name, item_base.artist_id, item_base.item_base_id, item_base.item_base_name, item_base.item_type, item_base.item_master, item_base.parent_item
        FROM item_base
        LEFT JOIN item_price ON item_base.item_base_id = item_price.item_base_id
        LEFT JOIN artist ON artist.artist_id = item_base.artist_id
        WHERE item_base.artist_id = ? AND item_base.item_type = ?
        GROUP BY item_base.item_base_id 
        ORDER BY prices_count DESC";
        $a = array($artist_id, $item_type);
        return pdoQueryAssocRows($this->m_dbPDO, $q, $a );
    }
    
    
    /** Get all artists with item_master albums */
    public function getArtistsWithItemMasterAlbums( $start, $count )
    {
        $limit = "LIMIT $start, $count";
        if ( $count == 0 ) $limit = '';
        $q = "SELECT DISTINCT artist_id
            FROM item_base
            WHERE item_master = 1
            ORDER BY artist_id ASC
            ${limit}";
        return pdoQueryAllRowsFirstElemAsInt($this->m_dbPDO, $q, array() );
    }
    
    /** Get all data needed to display an artist page */
    public function getPageData ($artist_id)
    {
        // TODO: Implement this!
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

        if ( skipDbWrite() ) return 0;
        $stmt = $this->m_dbPDO->prepare("INSERT INTO artist (artist_name) VALUES (?)" );
        $stmt->execute( array($artist_name) );

        $artist_id = (int)$this->m_dbPDO->lastInsertId();
        
        // Create entry in info_artist also 
        $stmt = $this->m_dbPDO->prepare("INSERT INTO info_artist (artist_id) VALUES (?)" );
        $stmt->execute(array($artist_id));
        return $artist_id;
    }
    
     /**  Create new artist with all fields
    \return ID of new artist. */
    public function createNewFull ($artist_name, $artist_genre_id, $country_id, $gender, $artist_real_name, $url_artist_official, $url_fanpage, $url_wikipedia, $url_allmusic, $url_musicbrainz, $url_discogs, $artist_type, $year_born, $year_died, $year_start, $year_end, $google_score, $bing_score, $url_facebook)
    {
        $artist_soundex = calcSoundex($artist_name);
        $stmt = $this->m_dbPDO->prepare("INSERT INTO artist (artist_name, genre_id, country_id, artist_soundex) VALUES (?, ?, ?, ?)");
        $stmt->execute( array($artist_name, $artist_genre_id, $country_id, $artist_soundex) );

        $artist_id = (int)$this->m_dbPDO->lastInsertId();
   
        // Create entry in info_artist also 
        $stmt = $this->m_dbPDO->prepare("INSERT INTO info_artist (artist_id, gender, artist_real_name, url_artist_official, url_fanpage, url_wikipedia, url_allmusic, url_musicbrainz, url_discogs, artist_type, country_id, year_born, year_died, year_start, year_end, google_score, bing_score, url_facebook) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute(array($artist_id, $gender, $artist_real_name, $url_artist_official, $url_fanpage, $url_wikipedia, $url_allmusic, $url_musicbrainz, $url_discogs, $artist_type, $country_id, $year_born, $year_died, $year_start, $year_end, $google_score, $bing_score, $url_facebook));
        return $artist_id;
    }
    
    /**  Update base data of existing artist. */
    public function updateBaseData ($aData)
    {
        $result = 0;
        $artist_id = (int)$aData['artist_id'];

        $aUpd = pdoGetUpdate ($aData, ArtistDataMySql::$m_aArtistTblFields );
        if ($aUpd[0] != "" ) {
            $q = 'UPDATE artist SET ' . $aUpd[0] . ' WHERE artist_id = ?';
            $aUpd[1][] = $artist_id;
            $stmt = $this->m_dbPDO->prepare($q);
            $stmt->execute($aUpd[1]);
            $result += $stmt->rowCount();
        }

        $aUpd = pdoGetUpdate ($aData, ArtistDataMySql::$m_aInfoArtistTblFields  );
        if ($aUpd[0] != "" ) {
            $q = 'UPDATE info_artist SET ' . $aUpd[0] . ' WHERE artist_id = ?';
            $aUpd[1][] = $artist_id;
            $stmt = $this->m_dbPDO->prepare($q);
            $stmt->execute($aUpd[1]);
            $result += $stmt->rowCount();
        }

        // Note: This $aData['artist_article'] != '' check is for efficiency reasons only!
        if ( $aData['artist_article'] != '' ) {
            // Since we do not create artist_text entries for every artist,language combination and since 
            // we want updateBaseData to work tranparently in this situation we add the follwing check 
            // and textCreateNewFull call if needed.
            $language_code = $aData['language_code'];
            if ( !$this->textExists ($artist_id, $language_code) ) {
                $id = $this->textCreateNewFull ($artist_id, $language_code, $aData['artist_article'], $aData['artist_text_reliability']);
                $result += 0 == $id ? 0 : 1;
            }
            else {
                $aUpd = pdoGetUpdate ($aData, ArtistDataMySql::$m_aArtistTextTblFields );
                if ($aUpd[0] != ""  && $artist_id != 0 && $language_code != '' ) {
                    $q = 'UPDATE artist_text SET ' . $aUpd[0] . ' WHERE artist_id = ? AND language_code = ?';
                    $aUpd[1][] = $artist_id;
                    $aUpd[1][] = $language_code;
                    $stmt = $this->m_dbPDO->prepare($q);
                    $stmt->execute($aUpd[1]);
                    $result += $stmt->rowCount();
                }
            }
        }
        return $result;
    }
    
    /**  Update base data of existing artist, but checking against the data already in DB and 
        only overwites non-empty values if new data has higher reliability (artist_reliability, info_artist_reliability) . */
    public function updateBaseDataCheckOld ($aData)
    {
        $result = 0;
        $artist_id = (int)$aData['artist_id'];
        $aDataOld = $this->getBaseData($artist_id);

        $aUpd = pdoGetUpdate ($aData, ArtistDataMySql::$m_aArtistTblFields, $aDataOld, 'artist_reliability' );
        if ($aUpd[0] != "" ) {
            $q = 'UPDATE artist SET ' . $aUpd[0] . ' WHERE artist_id = ?';
            $aUpd[1][] = $artist_id;
            $stmt = $this->m_dbPDO->prepare($q);
            $stmt->execute($aUpd[1]);
            $result += $stmt->rowCount();
        }

        $aUpd = pdoGetUpdate ($aData, ArtistDataMySql::$m_aInfoArtistTblFields, $aDataOld, 'info_artist_reliability' );
        if ($aUpd[0] != "" ) {
            $q = 'UPDATE info_artist SET ' . $aUpd[0] . ' WHERE artist_id = ?';
            $aUpd[1][] = $artist_id;
            $stmt = $this->m_dbPDO->prepare($q);
            $stmt->execute($aUpd[1]);
            $result += $stmt->rowCount();
        }
        
        // Note: This $aData['artist_article'] != '' check is for efficiency reasons only!
        if ( $aData['artist_article'] != '' ) {
            // Since we do not create artist_text entries for every artist,language combination and since 
            // we want updateBaseData to work tranparently in this situation we add the follwing check 
            // and textCreateNewFull call if needed.
            $language_code = $aData['language_code'];
            if ( !$this->textExists ($artist_id, $language_code) ) {
                $id = $this->textCreateNewFull ($artist_id, $language_code, $aData['artist_article'], $aData['artist_text_reliability']);
                $result += 0 == $id ? 0 : 1;
            }
            else {
                $aUpd = pdoGetUpdate ($aData, ArtistDataMySql::$m_aArtistTextTblFields, $aDataOld, 'artist_text_reliability' );
                if ($aUpd[0] != "" && $artist_id != 0 && $language_code != '' ) {
                    $q = 'UPDATE artist_text SET ' . $aUpd[0] . ' WHERE artist_id = ? AND language_code = ?';
                    $aUpd[1][] = $artist_id;
                    $aUpd[1][] = $language_code;
                    $stmt = $this->m_dbPDO->prepare($q);
                    $stmt->execute($aUpd[1]);
                    $result += $stmt->rowCount();
                }
            }
        }
        return $result;
    }

    // --------------------------------------
    // --- Artist text/article functions ----
    // --------------------------------------

     /**  Create new artist_text. 
    \return ID of new artist_text. */
    public function textCreateNew ($artist_id, $language_code)
    {
        $stmt = $this->m_dbPDO->prepare("INSERT INTO artist_text (artist_id, language_code) VALUES (?,?)" );
        $stmt->execute( array($artist_id, $language_code) );
        $artist_text_id = (int)$this->m_dbPDO->lastInsertId();
        return $artist_text_id;
    }
    
     /**  Create new artist with all fields
    \return ID of new artist. */
    public function textCreateNewFull ($artist_id, $language_code, $artist_article, $artist_text_reliability )
    {
        $stmt = $this->m_dbPDO->prepare("INSERT INTO artist_text (artist_id, language_code, artist_article, artist_text_reliability) VALUES (?,?,?,?)" );
        $stmt->execute( array($artist_id, $language_code, $artist_article, $artist_text_reliability) );
        $artist_text_id = (int)$this->m_dbPDO->lastInsertId();
        return $artist_text_id;
    }
    
     /**  Check if a given alias exists for artist. 
    \return ID of artist_text or zero if not found.. */
    public function textExists ($artist_id, $language_code)
    {
        if ( $artist_id == 0 || $language_code == '' ) return false;
        $q = "SELECT artist_text_id FROM artist_text WHERE artist_id = ? AND language_code = ?";
        return pdoLookupSingleIntQuery($this->m_dbPDO, $q, array($artist_id, $language_code) );
    }
     
     /**  Get artist text data. 
    \return Artist article text. */
    public function textDataGet ($artist_id, $language_code)
    {
        if ( $artist_id == 0 || $language_code == '' ) return '';
        $q = "SELECT * FROM artist_text WHERE artist_id = ? AND language_code = ?";
        return pdoQueryAssocFirstRow($this->m_dbPDO, $q, array($artist_id, $language_code) );
    }


     /**  Get all artists texts for saving as new json format. 
    \note This is not a general useable function as such since we dont return all data, but only what we need
            for our new json/disk/redis DB format.. */
    public function textDataGetAllForJson ($artist_id )
    {
        if ( $artist_id == 0 ) return array();
        $q = "SELECT language_code, created_date, artist_article, artist_text_reliability FROM artist_text WHERE artist_id = ?";
        return pdoQueryAssocRows($this->m_dbPDO, $q, array($artist_id) );
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
    static          $m_aArtistTblFields     = array( 'artist_name', 'artist_url', 'genre_id', 'subgenre_id', 'country_id', 'artist_reliability', 'artist_soundex' );
    static          $m_aInfoArtistTblFields = array( 'artist_id', 'gender', 'artist_real_name', 'url_artist_official', 'url_fanpage', 'url_wikipedia', 'url_allmusic', 'url_musicbrainz', 'url_discogs', 'artist_type', 'country_id', 'year_born', 'year_died', 'year_start', 'year_end', 'google_score', 'bing_score', 'url_facebook', 'info_artist_reliability' );
    static          $m_aArtistTextTblFields = array( 'language_code', 'artist_id', 'artist_article', 'artist_text_reliability' );
    
}


?>