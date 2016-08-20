<?php

require_once ('db_api/UnknownGenresDataMySql.php');

/** Class that handles conversion between IDs and official AP genre names for the 
currently 14 primary genres we use. See the derived GenreLookup class, which is used 
when reading data from XML. This handles translating lots of obscure genre names 
into one of the official ones. This class is intended for fast lookup when generating 
web pages. */
class GenreConvert
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct()
    {
    }

    /** Get genre ID from its (official AP genre) name.
    \return AP ID of primary genre name or zero if string is not an AP genre name.  */
    public function nameToID ($genre_name)
    {
        $s = mb_strtolower( $genre_name, 'UTF-8' );
        return $this->nameToIDLowerCase($s);
    }
    

    /** Get genre ID from its (official AP genre) name. The \a genre_name must be in lowercase. 
    \sa genreNameToID  that does not need it's argument to be in lowercase.
    \return AP ID of primary genre name or zero if string is not an AP genre name.  */
    public function nameToIDLowerCase ($genre_name)
    {
        return (int)($this->m_aGenreToID[$genre_name]);
    }
    
    /** Get genre name from it's ID.
    \return AP primary genre name or empty if ID is not an AP genre ID.  */
    public function IDToName($genre_id)
    {
        return (string)$this->m_aIDToGenre[$genre_id];
    }
    
    /** Array string representing the genres as needed in for example jeditable 'selects'.
    The array returned looks like this when converted using json_encode:
    {'0':'Unknown','1':'Pop/Rock','2':'Soul/R&B', ..., 'selected':'$iSelectedIndex'}
    \param $iSelectedIndex Is the index which should be selected. Eg. 1: Pop/Rock.
    \see admin_site/classes/ArtistPageUI.php for an example of it's use. It's very simple */
// // //    public function jsonSelect($iSelectedIndex)
    public function arrayForSelect($selectedGenreID)
    {
        $a = array();
        $N = count($this->m_aIDToGenre);
        for ( $i = 0; $i < $N; $i++ ) {
            $a[$i] = $this->m_aIDToGenre[$i];
        }
        $a['selected'] = $selectedGenreID;
        return $a;
    }
    
    // --------------------------
    // --- PRIVATE: Functions --- 
    // --------------------------
    
    // ---------------------
    // --- PRIVATE: Data --- 
    // ---------------------

    private     $m_aGenreToID = array (
         'pop/rock'             => 1
        ,'soul/r&b'             => 2
        ,'dance/electronic'     => 3
        ,'hiphop/rap'           => 4
        ,'metal/hard rock'      => 5
        ,'country/folk'         => 6
        ,'jazz/blues'           => 7
        ,'classical'            => 8
        ,'entertainment'        => 9
        ,'kids'                 => 10
        ,'other'                => 11
        ,'new age'              => 12
        ,'world/reggae'         => 13
        ,'soundtrack'           => 14
    );

    private     $m_aIDToGenre = array (
         ''   
        ,'Pop/Rock'
        ,'Soul/R&B'
        ,'Dance/Electronic' 
        ,'HipHop/Rap'
        ,'Metal/Hard Rock'
        ,'Country/Folk'
        ,'Jazz/Blues'
        ,'Classical'
        ,'Entertainment'
        ,'Kids'
        ,'Other'
        ,'New age'
        ,'World/Reggae'
        ,'Soundtrack'
    );
    
}


/** Lookup of all kinds of genres and convert them to one of the currently 14 official 
genre names / IDs we have :
--- Airplay genres ---
1    Pop/Rock
2    Soul/R&B
3    Dance/Electronic
4    HipHop/Rap
5    Metal/Hard Rock
6    Country/Folk
7    Jazz/Blues
8    Classical
9    Entertainment
10   Kids
11   Other
12   New age
13   World/Reggae
14   Soundtrack


*/
class GenreLookup extends GenreConvert
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $dbPDO = null )
    {
        $this->m_dbUnknownGenres = new UnknownGenresDataMySql($dbPDO);
    }

    
    
    
    /** Lookup genre official genre name from a genre name.
    \return AP ID of primary genre name or zero if string is not an AP genre name.  */
    public function latestUnknownLookup ()
    {
        return $this->m_sLatestUnknownGenreLookup;
    }
    
    /**  Saves the unknown genre_name, to DB along with record_store_id and buy_at_url 
         (where the unknown genere name was found). 
        \return ID of new or updated item. */
    public function latestUnknownSaveToDB ($record_store_id, $buy_at_url)
    {
        if (  $this->m_sLatestUnknownGenreLookup == '' )  return 0;
        return $this->m_dbUnknownGenres->setData ( $this->m_sLatestUnknownGenreLookup, $record_store_id, $buy_at_url  );
    }

    /**  Saves the unknown genre_name, to DB along with record_store_id and buy_at_url 
         (where the unknown genere name was found). 
        \return ID of new or updated item. */
    public function latestUnknownSave ($aData)
    {
        return $this->latestUnknownSaveToDB ( $aData['record_store_id'], $aData['buy_at_url']  );
    }
    
    /** Lookup genre_id  a genre name. Genre name can be anything that we can convert to 
    our official genre names, see m_toAirplayGenreContains member array.
    \return AP ID of primary genre name or zero if string cannot be converted to an AP genre name.  */
    public function nameToIDSimple ($genre)
    {
        return $this->lookupID($genre);
    }

    
    /** Lookup genre_id  a genre name. Genre name can be anything that we can convert to 
    our official genre names, see m_toAirplayGenreContains member array.
    \return AP ID of primary genre name or zero if string cannot be converted to an AP genre name.  */
    public function lookupID ($genre)
    {
        $genre = mb_strtolower( $genre, 'UTF-8' );

        $this->m_sLatestUnknownGenreLookup = '';
        $genre_id = 0;

        foreach ( $this->m_toAirplayGenreContains as $test => $airplay_genre_id ) {
            if ( strpos( $genre, $test ) !== false )   $genre_id = $airplay_genre_id;
        }

        if ( $genre_id == 0 )  $this->m_sLatestUnknownGenreLookup = $genre;
        return (int)$genre_id;
    }

    
    // --------------------------
    // --- PRIVATE: Functions --- 
    // --------------------------

   
    // ---------------------
    // --- PRIVATE: Data --- 
    // ---------------------
    private         $m_dbUnknownGenres = null;
    private         $m_sLatestUnknownGenreLookup;
    
    private $m_toAirplayGenreContains = array (
         '_UNKNOWN_'            => 0
        ,'pop/rock'             => 1
        ,'soul/r&b'             => 2
        ,'dance/electronic'     => 3
        ,'hiphop/rap'           => 4
        ,'metal/hard rock'      => 5
        ,'country/folk'         => 6
        ,'jazz/blues'           => 7
        ,'classical'            => 8
        ,'entertainment'        => 9
        ,'kids'                 => 10
        ,'other'                => 11
        ,'new age'              => 12
        ,'world/reggae'         => 13
        ,'soundtrack'           => 14
// ----------------------------------------------        
        ,'child'                => 10
        ,'barn'                 => 10
        ,'hard rock'            => 5
        ,'hard'                 => 5
        ,'heavy'                => 5
        ,'metal'                => 5
        ,'pop'                  => 1
        ,'rock'                 => 1
        ,'disco'                => 1
        ,'dance'                => 3
        ,'elektronisk'          => 3
        ,'klassisk'             => 8
        ,'world'                => 13
        ,'jazz'                 => 7
        ,'swing'                => 7
        ,'blues'                => 7
        ,'alternative'          => 12
        ,'soul'                 => 2
        ,'r&b'                  => 2
        ,'r & b'                => 2
        ,"r'n'b"                => 2
        ,"r'n'b"                => 2
        ,'country'              => 6
        ,'americana'            => 6
        ,'folk'                 => 6
        ,'reggae'               => 13
        ,'international'        => 13
        ,'african'              => 13
        ,'etnisk'               => 13
        ,'gammeldans'           => 13
        ,'verdens'              => 13
        ,'tradisjon'            => 13
        ,'tango'                => 13
        ,'samba'                => 13
        ,'latin'                => 13
        ,'holiday'              => 11
        ,'børn'                 => 10
        ,'comedy'               => 9
        ,'wellness'             => 12
        ,'jul'                  => 11
        ,'dance'                => 3
        ,'trance'               => 3
        ,'house'                => 3
        ,'club'                 => 3
        ,'techno'               => 3
        ,'rap'                  => 4
        ,'hiphop'               => 4
        ,'hip-hop'              => 4
        ,'hip hop'              => 4
        ,'hip/hop'              => 4
        ,'punk'                 => 1
        ,'new age'              => 12
        ,'newage'               => 12
        ,'electronic'           => 3
        ,'underholdning'        => 9
        ,'danseband'            => 1
        ,'christmas'            => 11
        ,'oldies'               => 11
        ,'instrumental'         => 11
        ,'diverse'              => 11
        ,'religiøst'            => 11
        ,'religion'             => 11
        ,'christian'            => 11
        ,'gospel'               => 11
        ,'kunstmusikk'          => 11
        ,'lyrikk'               => 11
        ,'miscellaneous'        => 11
        ,'soundtrack'           => 14
        ,'musical'              => 14
        ,'alternativ'           => 12
        ,'ambient'              => 12
        ,'chillout'             => 12
        ,'easy listening'       => 12
        ,'indie'                => 12
        ,'kinder'               => 10
        ,'classic'              => 8
        ,'operette'             => 8
        ,'opera'                => 8
        ,'compilation'          => 11
        ,'dansk'                => 11
        ,'lounge'               => 12
        ,'schlager'             => 13      
    );
    
    // For use when adding new "translations"  we repeat the officail genre names and IDs here:
    // 1    pop/rock
    // 2    soul/r&b
    // 3    dance/electronic
    // 4    hiphop/rap
    // 5    metal/hard rock
    // 6    country/folk
    // 7    jazz/blues
    // 8    classical
    // 9    entertainment
    // 10   kids
    // 11   other
    // 12   new age
    // 13   world/reggae
    // 14   soundtrack
    
    
}



?>