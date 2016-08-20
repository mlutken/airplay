<?php


/** Class that handles conversion between IDs and official AP country names for the 
 countrys we use. See the derived CountryLookup class, which is used 
when reading data from XML. This handles translating lots of obscure country names 
into one of the official ones. This class is intended for fast lookup when generating 
web pages. */
class CountryConvert
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct()
    {
    }

    /** Get country ID from its (official AP country) name.
    \deprecated Use nameToID instead
    \return AP ID of primary country name or zero if string is not an AP country name.  */
    public function countryNameToID ($country_name)
    {
        $s = mb_strtolower( $country_name, 'UTF-8' );
        return $this->countryNameToIDLowerCase($s);
    }

    /** Get country ID from its (official AP country) name.
    \return AP ID of primary country name or zero if string is not an AP country name.  */
    public function nameToID ($country_name)
    {
        $s = mb_strtolower( $country_name, 'UTF-8' );
        return $this->countryNameToIDLowerCase($s);
    }
    

    /** Get country ID from its (official AP country) name. The \a country_name must be in lowercase. 
    \sa countryNameToID  that does not need it's argument to be in lowercase.
    \return AP ID of primary country name or zero if string is not an AP country name.  */
    public function countryNameToIDLowerCase ($country_name)
    {
        return (int)($this->m_aCountryToID[$country_name]);
    }
    
    /** Get country name from it's ID.
    \return AP primary country name or empty if ID is not an AP country ID.  */
    public function IDToName($country_id)
    {
        return (string)$this->m_aIDToCountry[$country_id];
    }
    
    
    /** Array string representing the COUNTRY NAMES as needed in for example jeditable 'selects'.
    The array returned looks like this when converted using json_encode:
    {'0':'Unknown','1':'Pop/Rock','2':'Soul/R&B', ..., 'selected':'$iSelectedIndex'}
    \param $iSelectedIndex Is the index which should be selected. Eg. 1: Pop/Rock.
    \see admin_site/Artistpage.php for an example of it's use. It's very simple */
    public function arrayForSelect($selectedCountryID)
    {
        $a = array();
        foreach ( $this->m_aIDToCountry as $id => $name ) {
            $a[$id] = $name;
        }
        $a['selected'] = $selectedCountryID;
        return $a;
    }
    
    // --------------------------
    // --- PRIVATE: Functions --- 
    // --------------------------
    
    // ---------------------
    // --- PRIVATE: Data --- 
    // ---------------------
    
    private     $m_aCountryToID = array (
         "usa"              => 1
        ,"canada"           => 2
        ,"russia"           => 7
        ,"greece"           => 30
        ,"holland"          => 31
        ,"belgium"          => 32
        ,"france"           => 33
        ,"spain"            => 34
        ,"italy"            => 39
        ,"switzerland"      => 41
        ,"austria"          => 43
        ,"uk"               => 44
        ,"denmark"          => 45
        ,"sweden"          => 46
        ,"norway"           => 47
        ,"germany"          => 49
        ,"mexico"           => 52
        ,"argentina"        => 54
        ,"brazil"           => 55
        ,"australia"        => 61
        ,"new zealand"      => 64
        ,"japan"            => 81
        ,"china"            => 86
        ,"turkey"           => 90
        ,"portugal"         => 351
        ,"ireland"          => 353
        ,"iceland"          => 354
        ,"finland"          => 358
        ,"hongkong"         => 852
        ,"israel"           => 972
    );

    private     $m_aIDToCountry = array (
          1     => "USA"
        , 2     => "Canada" 
        , 7     => "Russia" 
        , 30    => "Greece" 
        , 31    => "Holland" 
        , 32    => "Belgium" 
        , 33    => "France"
        , 34    => "Spain"
        , 39    => "Italy"
        , 41    => "Switzerland"
        , 43    => "Austria"
        , 44    => "UK" 
        , 45    => "Denmark"
        , 46    => "Sweden"
        , 47    => "Norway"
        , 49    => "Germany"
        , 52    => "Mexico"
        , 54    => "Argentina"
        , 55    => "Brazil"
        , 61    => "Australia"
        , 64    => "New Zealand"
        , 81    => "Japan"
        , 86    => "China"
        , 90    => "Turkey"
        , 351    => "Portugal"
        , 353    => "Ireland"
        , 354    => "Iceland"
        , 358   => "Finland"
        , 852   => "Hongkong"
        , 972   => "Israel"
    );
    
}


/** Lookup of all kinds of countrys and convert them to one of the official 
country names / IDs we have :
--- Airplay countrys ---
          1     => "USA"
        , 33    => "France"
        , 44    => "UK" 
        , 45    => "Denmark"
        , 46    => "Sweden"
        , 47    => "Norway"
        , 49    => "Germany"
        , 358   => "Finland"

*/
class CountryLookup extends CountryConvert
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $dbPDO = null )
    {
    }

    
    
    /** Lookup country_id  a country name. Country name can be anything that we can convert to 
    our official country names, see m_toAirplayCountryContains member array.
    \return AP ID of primary country name or zero if string cannot be converted to an AP country name.  */
    public function lookupID ($country)
    {
        $country = mb_strtolower( $country, 'UTF-8' );

        $this->m_sLatestUnknownCountryLookup = "";
        $country_id = 0;

        foreach ( $this->m_toAirplayCountryContains as $test => $airplay_country_id ) {
            if ( strpos( $country, $test ) !== false )   $country_id = $airplay_country_id;
        }

        if ( $country_id == 0 )  $this->m_sLatestUnknownCountryLookup = $country;
        return (int)$country_id;
    }

    
    // --------------------------
    // --- PRIVATE: Functions --- 
    // --------------------------

   
    // ---------------------
    // --- PRIVATE: Data --- 
    // ---------------------
    private         $m_dbUnknownCountrys = null;
    private         $m_sLatestUnknownCountryLookup;
    
    private $m_toAirplayCountryContains = array (
         "usa"                  => 1
        ,"france"               => 33
        ,"uk"                   => 44
        ,"denmark"              => 45
        ,"sweden"              => 46
        ,"norway"               => 47
        ,"germany"              => 49
        ,"finland"              => 358
// ----------------------------------------------        
        ,"danmark"              => 45
        ,"dk"                   => 45
        ,"sverige"              => 46
        ,"se"                   => 46
        ,"norge"                => 47
        ,"no"                   => 47
        ,"us"                   => 1
        ,"amerika"              => 1
        ,"america"              => 1
        ,"england"              => 44
        ,"britain"              => 44
        ,"hongkong"             => 852
    );
    
    
    
}



?>