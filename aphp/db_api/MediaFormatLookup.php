<?php

require_once ('db_api/UnknownMediaFormatsDataMySql.php');

/** Class that handles conversion between IDs and official AP media_format names for the 
currently 14 primary media_formats we use. See the derived MediaFormatLookup class, which is used 
when reading data from XML. This handles translating lots of obscure media_format names 
into one of the official ones. This class is intended for fast lookup when generating 
web pages.

$m_aMediaFormatToID - Exact mediaformat names to ID.
$m_aIDToMediaFormat - Mediaformat id to name.

$m_toAirplayMediaFormatContains - An array for converting substrings to MediaFormatID's
("sacd" needs to be before "cd" otherwise "cd" will never be found)
 */
class MediaFormatConvert
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct()
    {
    }

    /** Get media_format ID from its (official AP media_format) name.
    \return AP ID of primary media_format name or zero if string is not an AP media_format name.  */
    public function nameToIDSimple ($media_format_name)
    {
        $s = mb_strtolower( $media_format_name, 'UTF-8' );
        return $this->nameToIDLowerCase($s);
    }
    

    /** Get media_format ID from its (official AP media_format) name. The \a media_format_name must be in lowercase. 
    \sa mediaFormatNameToID  that does not need it's argument to be in lowercase.
    \return AP ID of primary media_format name or zero if string is not an AP media_format name.  */
    public function nameToIDLowerCase ($media_format_name)
    {
        return (int)($this->m_aMediaFormatToID[$media_format_name]);
    }
    
    /** Get media_format name from it's ID.
    \return AP primary media_format name or empty if ID is not an AP media_format ID.  */
    public function IDToName($media_format_id)
    {
        return (string)$this->m_aIDToMediaFormat[$media_format_id];
    }
    
    // --------------------------
    // --- PRIVATE: Functions --- 
    // --------------------------
    
    // ---------------------
    // --- PRIVATE: Data --- 
    // ---------------------
    private     $m_aMediaFormatToID = array (
         'wma'          => 1
        ,'acc'          => 2
        ,'mp3'          => 3
        ,'streaming'       => 4
        ,'cd'           => 5
        ,'vinyl'        => 7
        ,'dvd'          => 8
        ,'mobile'       => 9
        ,'blu-ray'      => 10
        ,'_RESERVED_'   => 11
        ,'sacd'         => 12
        ,'sacdh'        => 13
        ,'dvda'         => 14
        ,'mc'           => 15
		,'flac'           => 16
        ,'t-shirts'     => 64
        ,'hoodies'      => 65
        ,'jackets'      => 66
        ,'caps'         => 67
        ,'bags'         => 68
        ,'bedding'      => 69
        ,'posters'      => 70
        ,'mugs'         => 71
        ,'accessories'  => 72
        ,'other'        => 73
        ,'concert'      => 128
        ,'festival'     => 129
    );

    private     $m_aIDToMediaFormat = array (
          ''   
        , 1 => 'WMA'
        , 2 => 'ACC'
        , 3 => 'MP3' 
        , 4 => 'Streaming'
        , 5 => 'CD'
        , 7 => 'Vinyl'
        , 8 => 'DVD'
        , 9 => 'Mobile'
        , 10 => 'Blu-ray'
        , 11 => '_RESERVED_'
        , 12 => 'SACD'
        , 13 => 'SACDH'
        , 14 => 'DVDA'
        , 15 => 'MC'
		, 16 => 'FLAC'
        , 64 => 'T-shirts'
        , 65 =>'Hoodies'
        , 66 => 'Jackets'
        , 67 => 'Caps'
        , 68 => 'Bags'
        , 69 => 'Bedding'
        , 70 => 'Posters'
        , 71 => 'Mugs'
        , 72 => 'Accessories'
        , 73 => 'Other'
        , 128 => 'Concert'
        , 129 => 'Festival'
    );
    
}


/** Lookup of all kinds of media_formats and convert them to one of the currently 14 official 
media_format names / IDs we have :
--- Airplay media_formats ---
1   WMA
2   ACC
3   MP3
4   Stream
5   CD
6   Single
7   Vinyl
8   DVD
9   Mobile
10  Blu-ray
11  _RESERVED_
12  SACD
13  SACDH
14  DVDA
15  MC


*/
class MediaFormatLookup extends MediaFormatConvert
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $dbPDO = null )
    {
        $this->m_dbUnknownMediaFormats = new UnknownMediaFormatsDataMySql($dbPDO);
    }

    
    
    
    /** Lookup media_format official media_format name from a media_format name.
    \return AP ID of primary media_format name or zero if string is not an AP media_format name.  */
    public function latestUnknownLookup ()
    {
        return $this->m_sLatestUnknownMediaFormatLookup;
    }
    
    /**  Saves the unknown media_format_name, to DB along with record_store_id and buy_at_url 
         (where the unknown genere name was found). 
        \return ID of new or updated item. */
    public function latestUnknownSaveToDB ($record_store_id, $buy_at_url)
    {
        if (  $this->m_sLatestUnknownMediaFormatLookup == '' )  return false;
        return $this->m_dbUnknownMediaFormats->setData ( $this->m_sLatestUnknownMediaFormatLookup, $record_store_id, $buy_at_url  );
    }
    
    /**  Saves the unknown media_format_name, to DB along with record_store_id and buy_at_url 
         (where the unknown genere name was found). 
        \return ID of new or updated item. */
    public function latestUnknownSave ($aData)
    {
        return $this->latestUnknownSaveToDB ( $aData['record_store_id'], $aData['buy_at_url']  );
    }
    
    /** Lookup media_format_id  a media_format name. MediaFormat name can be anything that we can convert to 
    our official media_format names, see m_toAirplayMediaFormatContains member array.
    \return AP ID of primary media_format name or zero if string cannot be converted to an AP media_format name.  */
    public function lookupID ($media_format)
    {
        $media_format = mb_strtolower( $media_format, 'UTF-8' );

        $this->m_sLatestUnknownMediaFormatLookup = '';
        $media_format_id = 0;

        foreach ( $this->m_toAirplayMediaFormatContains as $test => $airplay_media_format_id ) {
            if ( strpos( $media_format, $test ) !== false )   $media_format_id = $airplay_media_format_id;
        }

        if ( $media_format_id == 0 )  $this->m_sLatestUnknownMediaFormatLookup = $media_format;
        return (int)$media_format_id;
    }

    public function dbg()
    {
        foreach ( $this->m_toAirplayMediaFormatContains as $test => $airplay_media_format_id ) {
            printf ( " $test => $airplay_media_format_id\n");
        }
    
    }
    
    // --------------------------
    // --- PRIVATE: Functions --- 
    // --------------------------

   
    // ---------------------
    // --- PRIVATE: Data --- 
    // ---------------------
    private         $m_dbUnknownMediaFormats = null;
    private         $m_sLatestUnknownMediaFormatLookup;
    
    private $m_toAirplayMediaFormatContains = array (
         '_UNKNOWN_'            => 0
        ,'sacdh'                => 13
        ,'sacd'                 => 12
        ,'dvda'                 => 14
        ,'dvd audio'            => 14
        ,'vinyl'                => 7
        ,'blu-ray'              => 10
        ,'bluray'               => 10
        ,'blu'                  => 10
        ,'ray'                  => 10
        ,'mobile'               => 9
        ,'streaming'               => 4
        ,'wma'                  => 1
        ,'acc'                  => 2
        ,'mp3'                  => 3
        ,'dvd'                  => 8
        ,'cd'                   => 5
        ,'lp'                   => 7
        ,'casette'              => 15
        ,'mc'                   => 15
		,'flac'                 => 16
        ,'12"'                  => 7
        ,'7"'                   => 7
        ,'12 inch'              => 7
        ,'7 inch'               => 7
        ,'t-shirt'              => 64
        ,'shirt'                => 64
        ,'skjorte'              => 64
        ,'hoodie'               => 65
        ,'jacket'               => 66
        ,'cap'                  => 67
        ,'bag'                  => 68
        ,'bedding'              => 69
        ,'poster '              => 70
        ,'mug'                  => 71
        ,'accessory'			=> 72
        ,'accessories'          => 72
        ,'other'                => 73
    );
    
    // For use when adding new "translations"  we repeat the official media_format names and IDs here:
    //  1   WMA
    //  2   ACC
    //  3   MP3
    //  4   Stream
    //  5   CD
    //  6   Single
    //  7   Vinyl
    //  8   DVD
    //  9   Mobile
    //  10  Blu-ray
    //  12  SACD
    //  13  SACDH
    //  14  DVDA
    //  15  MC
    //  15  FLAC
    
}



?>