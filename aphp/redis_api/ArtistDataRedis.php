<?php
require_once ("redis_api/redis_utils.php");


class ArtistDataRedis
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $redis = null )
    {
        global $g_redis;
        $this->m_r = $redis;
        if ( $redis == null ) $this->m_r = $g_redis;      
    }

    // ----------------------------------
    // --- Item ID lookup functions --- 
    // ----------------------------------

    /** Lookup artist ID from its name.
    \return One artist ID if found one and only one matching. Zero if not found any and -N if N matching found.  */
    public function nameToID ($artist_name)
    {
        $ln = toLookUpName($artist_name); 
        return $this->m_r->get( $ln . ':artist_id' );
    }

    /** Lookup artist ID from artist alias name.
    \return one artist ID if found one and only one matching. Zero if not found any and -n if more than one (n) found.  */
    public function aliasToID ($alias_name)
    {
        return $this->m_r->hget('artist_alias_ids', $alias_name );
    }
    
    /** Lookup artist ID from  artist name or alias.
    \return artist_id if found one and only one matching. Zero if not found any and -n if more than one (n) found.  */
    public function lookupID ($artist_name)
    {
        $id = $this->nameToID ($artist_name);
        if ( $id == 0   )  $id = $this->aliasToID ($artist_name);
        return $id;
    }

    
    // --------------------------
    // --- Get data functions --- 
    // --------------------------
    /** Get artist base data. Eg. XX ... etc. */
    public function getBaseData ($artist_id)
    {
        return $this->m_r->hgetall( $artist_id . ':d' );
    }

    /** Get artist official name from ID. 
    \return Official AP name. */
    public function IDToName ($artist_id)
    {
        return $this->m_r->hget( $artist_id . ':d', 'artist_name' );
    }
    
    
    // --------------------------
    // --- Set data functions --- 
    // --------------------------
    /**  Set base data of item. Creates new item if name not found. */
    public function setBaseData ($aData)
    {

    }

    
    
    /**  Create new item. 
    \return ID of new item. */
    public function createNew ( $artist_name )
    {
        if ( skipDbWrite() ) return 0;
        $id = nextGlobalID($this->m_r );
        $this->m_r->lpush("artist:all", $id);         // Push ID to list of all artists
        $this->m_r->set($id . ':type', 'artist' );
        $ln = toLookUpName($artist_name); 
        $this->m_r->lpush($ln . ':ids', $id  );       // Add name to global lookup. Since we could have song names etc. with same name as a artist we add to a list here
        $this->m_r->set( $ln . ':artist_id', $id  );  // Add name to exact artist lookup. 
//         $this->m_r->lpush( $ln . ':artist_ids', $id  );  // TODO: Perhaps use list instead! 

        // --- BaseData hash (those from m_aDataTblFields) ---  
        $this->m_r->hset($id . ':d', 'artist_id', $id );
        $this->m_r->hset($id . ':d', 'artist_name', $artist_name );
        return $id;
    }
    
    /**  Create new item with all fields
    \return ID of new item. */
    public function createNewFull (   $artist_name, $artist_genre_id, $country_id, $gender, $artist_real_name
                                    , $url_artist_official, $url_fanpage, $url_wikipedia, $url_allmusic, $url_musicbrainz
                                    , $url_discogs, $artist_type, $year_born, $year_died, $year_start, $year_end
                                    , $google_score, $bing_score)
    {
        if ( skipDbWrite() ) return 0;
        $id = $this->createNew ( $artist_name );
        $this->m_r->hset($id . ':d', 'artist_genre_id', $artist_genre_id );
        $this->m_r->hset($id . ':d', 'country_id', $country_id );
        $this->m_r->hset($id . ':d', 'gender', $gender );
        $this->m_r->hset($id . ':d', 'artist_real_name', $artist_real_name );
        $this->m_r->hset($id . ':d', 'url_artist_official', $url_artist_official );
        $this->m_r->hset($id . ':d', 'url_fanpage', $url_fanpage );
        $this->m_r->hset($id . ':d', 'url_wikipedia', $url_wikipedia );
        $this->m_r->hset($id . ':d', 'url_allmusic', $url_allmusic );
        $this->m_r->hset($id . ':d', 'url_allmusic', $url_allmusic );
        $this->m_r->hset($id . ':d', 'url_musicbrainz', $url_musicbrainz );
        $this->m_r->hset($id . ':d', 'url_discogs', $url_discogs );
        $this->m_r->hset($id . ':d', 'artist_type', $artist_type );
        $this->m_r->hset($id . ':d', 'year_born', $year_born );
        $this->m_r->hset($id . ':d', 'year_died', $year_died );
        $this->m_r->hset($id . ':d', 'year_start', $year_start );
        $this->m_r->hset($id . ':d', 'year_end', $year_end );
        $this->m_r->hset($id . ':d', 'google_score', $google_score );
        $this->m_r->hset($id . ':d', 'bing_score', $bing_score );
        return $id;
    }
    
    
    /**  Update base data of existing item. */
    public function updateBaseData ($aData)
    {
        $artist_id = $aData['artist_id'];
        redisHashSetData ( $this->m_r, $artist_id, $aData, ArtistDataRedis::$m_aDataTblFields );
    }
    
    /**  Update base data of existing item, but checking against the data already in DB and 
        only overwites non-empty values if new data has higher reliability (artist_reliability). */
    public function updateBaseDataCheckOld ($aData)
    {
        $artist_id = $aData['artist_id'];
        $aDataOld = $this->getBaseData($artist_id);
        redisHashSetData ( $this->m_r, $artist_id, $aData, ArtistDataRedis::$m_aDataTblFields,  $aDataOld, 'artist_reliability' );
    }
    
    
    // ---------------------------------
    // --- Alias handling functions ----
    // ---------------------------------
     /**  Check if a given alias exists for artist. 
    \return ID of alias or zero if not found. */
    public function aliasExists ($artist_id, $alias_name)
    {
        return $this->m_r->hexists('artist_alias_ids', $alias_name );
    }

    
    /**  Create new alias for artist. 
    \return void */
    public function aliasCreateNew ($artist_id, $alias_name)
    {
        $this->m_r->hset( 'artist_alias_ids', $alias_name, $artist_id );
    }

    /**  Delete alias for artist. 
    \return DB execute result. */ 
    public function aliasDeleteByName ($artist_id, $alias_name)
    {
        $this->m_r->hdel( 'artist_alias_ids', $alias_name );
    }

    
    // --------------------------
    // --- PRIVATE: Functions --- 
    // --------------------------
    
    // ---------------------
    // --- PRIVATE: Data --- 
    // ---------------------
    private         $m_r = null;
    public static   $m_aDataTblFields = array(   'artist_name', 'artist_genre_id'
                                                , 'subgenre_id', 'country_id', 'gender', 'artist_real_name'
                                                , 'url_artist_official', 'url_fanpage', 'url_wikipedia', 'url_allmusic'
                                                , 'url_musicbrainz', 'url_discogs', 'artist_type', 'country_id'
                                                , 'year_born', 'year_died', 'year_start', 'year_end'
                                                , 'google_score', 'bing_score', 'artist_reliability' 
                                            );

    
}

?>