<?php

require_once ('db_string_utils.php');
require_once ('ArtistDataMySql.php');
require_once ('CountryLookup.php');
require_once ('GenreLookup.php');


class DbInserter_artist_data 
{

    public  function    __construct(  $dbPDO = null )
    {
        global $g_MySqlPDO;
        $this->m_dbPDO = $dbPDO;
        if ( $dbPDO == null ) $this->m_dbPDO = $g_MySqlPDO; 
        
        $this->m_dbCountryLookup    = new CountryLookup     ( $this->m_dbPDO );
        $this->m_dbGenreLookup      = new GenreLookup       ( $this->m_dbPDO );
        $this->m_dbArtistData       = new ArtistDataMySql   ( $this->m_dbPDO );
        
    }

 
    public function insertToDB ( $aData )
    {
        printf("DbInserter_artist_data insertToDB\n" );
        
        $alias_name             = '';
        $artist_name            = $aData['artist_name'];
        $artist_id              = $this->m_dbArtistData->lookupID( $artist_name );
        
        // Try reversed lookup for two word names with comma between the words (ex. 'Turner, Tina')
        if ( $artist_id <= 0 ) {
            $artist_name_reversed = reverseArtistNameWithComma($artist_name);
            printf("artist_name_reversed 1: $artist_name_reversed\n");
            if ( $artist_name_reversed != $artist_name ) {
                printf("Lookup reversed name 1: $artist_name_reversed\n");
                //$alias_name
                $artist_id = $this->m_dbArtistData->nameToID( $artist_name_reversed );
                if ( $artist_id > 0 ) {
                    $alias_name = $artist_name;
                }
            }
        }
        
        // Try reversed lookup for two word names without comma (ex. 'Turner Tina')
        if ( $artist_id <= 0 ) {
            $artist_name_reversed = reverseArtistName($artist_name);
            printf("artist_name_reversed 2: $artist_name_reversed\n");
            if ( $artist_name_reversed != $artist_name ) {
                printf("Lookup reversed name 2: $artist_name_reversed\n");
                //$alias_name
                $artist_id = $this->m_dbArtistData->nameToID( $artist_name_reversed );
                if ( $artist_id > 0 ) {
                    $alias_name = $artist_name;
                }
            }
        }
        $this->doUpdateArtistData ( $aData, $artist_id, $alias_name );
    }

    // --------------------------
    // --- PRIVATE: Functions --- 
    // --------------------------
    private function doUpdateArtistData ( $aData, $artist_id, $alias_name )
    {
        if ( $artist_id == 0 ) {
            $artist_id = $this->m_dbArtistData->createNew($aData['artist_name']);
        }
        if ( $alias_name != '' ) {
            if ( ! $this->m_dbArtistData->aliasExists ($artist_id, $alias_name) ) {
                $this->m_dbArtistData->aliasCreateNew ($artist_id, $alias_name);
            }
        }
        
        $aData['artist_id']         = $artist_id;
        $aData['country_id']        = $this->m_dbCountryLookup->lookupID($aData['country_name']);
        $aData['artist_genre_id']   = $this->m_dbGenreLookup->lookupID($aData['artist_genre_name']);

        // Make 100% sure that we have the official AP artist_name in aData before updating
        $aData['artist_name']       = $this->m_dbArtistData->IDToName($artist_id); 
        var_dump( $aData );
    }

    
    private function lookupID ( $artist_name )
    {
        $aId = $this->m_dbArtistData->lookupID( $artist_name );
        
        // If we found an ID from an alias name, then lookup the official AP artist name
        if ( $aId[0] > 0 && $aId[1] == 1 )  $artist_name = $this->m_dbArtistData->IDToName( $aId[0] );
        $aId[] = $artist_name;  // Add offical artist_name as 3rd element of return array
        return $aId;
    }
    
    private function checkIdAndWarn ( $artist_id, $artist_name_new )
    {
        //logDbInsertWarning( $msg )
    }
    // ----------------------------
    // --- PRIVATE: Member data --- 
    // ----------------------------
    private         $m_dbPDO            = null;
    private         $m_dbCountryLookup;
    private         $m_dbGenreLookup;
    private         $m_dbArtistData;
}


?>







