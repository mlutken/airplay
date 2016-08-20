<?php

require_once ('db_api/UnknownMediaTypesDataMySql.php');

/** Class that handles conversion between IDs and official AP media_type names for the 
currently 14 primary media_types we use. See the derived MediaTypeLookup class, which is used 
when reading data from XML. This handles translating lots of obscure media_type names 
into one of the official ones. This class is intended for fast lookup when generating 
web pages. */
class MediaTypeConvert
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct()
    {
    }

    /** Get media_type ID from its (official AP media_type) name.
    \return AP ID of primary media_type name or zero if string is not an AP media_type name.  */
    public function nameToIDSimple ($media_type_name)
    {
        $s = mb_strtolower( $media_type_name, 'UTF-8' );
        return $this->nameToIDLowerCase($s);
    }
    

    /** Get media_type ID from its (official AP media_type) name. The \a media_type_name must be in lowercase. 
    \sa mediaTypeNameToID  that does not need it's argument to be in lowercase.
    \return AP ID of primary media_type name or zero if string is not an AP media_type name.  */
    public function nameToIDLowerCase ($media_type_name)
    {
        return (int)($this->m_aMediaTypeToID[$media_type_name]);
    }
    
    /** Get media_type name from it's ID.
    \return AP primary media_type name or empty if ID is not an AP media_type ID.  */
    public function IDToName($media_type_id)
    {
        return (string)$this->m_aIDToMediaType[$media_type_id];
    }
    
    // --------------------------
    // --- PRIVATE: Functions --- 
    // --------------------------
    
    // ---------------------
    // --- PRIVATE: Data --- 
    // ---------------------
    private     $m_aMediaTypeToID = array (
         'audio'          => 1
        ,'video'          => 2
        ,'merchandise'    => 3
        ,'concert'        => 4
    );

    private     $m_aIDToMediaType = array (
          ''   
        , 'audio'
        , 'video'
        , 'merchandise'
        , 'concert'
    );
    
}


/** Lookup of all kinds of media_types and convert them to one of the currently 14 official 
media_type names / IDs we have :
--- Airplay media_types ---
1   audio
2   video
3   merchandise
4   concert

*/
class MediaTypeLookup extends MediaTypeConvert
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $dbPDO = null )
    {
        $this->m_dbUnknownMediaTypes = new UnknownMediaTypesDataMySql($dbPDO);
    }

    
    
    
    /** Lookup media_type official media_type name from a media_type name.
    \return AP ID of primary media_type name or zero if string is not an AP media_type name.  */
    public function latestUnknownLookup ()
    {
        return $this->m_sLatestUnknownMediaTypeLookup;
    }
    
    /**  Saves the unknown media_type_name, to DB along with record_store_id and buy_at_url 
         (where the unknown genere name was found). 
        \return ID of new or updated item. */
    public function latestUnknownSaveToDB ($record_store_id, $buy_at_url)
    {
        if (  $this->m_sLatestUnknownMediaTypeLookup == '' )  return false;
        return $this->m_dbUnknownMediaTypes->setData ( $this->m_sLatestUnknownMediaTypeLookup, $record_store_id, $buy_at_url  );
    }
    
    /**  Saves the unknown media_type_name, to DB along with record_store_id and buy_at_url 
         (where the unknown genere name was found). 
        \return ID of new or updated item. */
    public function latestUnknownSave ($aData)
    {
        return $this->latestUnknownSaveToDB ( $aData['record_store_id'], $aData['buy_at_url']  );
    }
    
    /** Lookup media_type_id  a media_type name. MediaType name can be anything that we can convert to 
    our official media_type names, see m_toAirplayMediaTypeContains member array.
    \return AP ID of primary media_type name or zero if string cannot be converted to an AP media_type name.  */
    public function lookupID ($media_type)
    {
        $media_type = mb_strtolower( $media_type, 'UTF-8' );

        $this->m_sLatestUnknownMediaTypeLookup = '';
        $media_type_id = 0;

        foreach ( $this->m_toAirplayMediaTypeContains as $test => $airplay_media_type_id ) {
            if ( strpos( $media_type, $test ) !== false )   $media_type_id = $airplay_media_type_id;
        }

        if ( $media_type_id == 0 )  $this->m_sLatestUnknownMediaTypeLookup = $media_type;
        return (int)$media_type_id;
    }

    public function dbg()
    {
        foreach ( $this->m_toAirplayMediaTypeContains as $test => $airplay_media_type_id ) {
            printf ( " $test => $airplay_media_type_id\n");
        }
    
    }
    
    // --------------------------
    // --- PRIVATE: Functions --- 
    // --------------------------
   
    // ---------------------
    // --- PRIVATE: Data --- 
    // ---------------------
    private         $m_dbUnknownMediaTypes = null;
    private         $m_sLatestUnknownMediaTypeLookup;
    
    private $m_toAirplayMediaTypeContains = array (
         '_UNKNOWN_'            => 0
        ,'audio'                => 1
        ,'video'                => 2
        ,'dvda'                 => 1
        ,'dvd audio'            => 1
        ,'vinyl'                => 1
        ,'blu-ray'              => 2
        ,'bluray'               => 2
        ,'blu'                  => 2
        ,'ray'                  => 2
        ,'mobile'               => 1
        ,'single'               => 1
        ,'streaming'               => 1
        ,'wma'                  => 1
        ,'acc'                  => 1
        ,'mp3'                  => 1
        ,'dvd'                  => 2
        ,'cd'                   => 1
        ,'lp'                   => 1
        ,'casette'              => 1
        ,'mc'                   => 1
        ,'12"'                  => 1
        ,'7"'                   => 1
        ,'12 inch'              => 1
        ,'7 inch'               => 1
        ,'t-shirt'              => 3
        ,'shirt'                => 3
        ,'skjorte'              => 3
        ,'hoodie'               => 3
        ,'jacket'               => 3
        ,'cap'                  => 3
        ,'bag'                  => 3
        ,'bedding'              => 3
        ,'poster '              => 3
        ,'mug'                  => 3
        ,'accessory'			=> 3
        ,'accessories'          => 3
    );
    
    // For use when adding new "translations" we repeat the official media_type names and IDs here:
    // 1   audio
    // 2   video
    // 3   merchandise
    // 4   concert
    
}



?>