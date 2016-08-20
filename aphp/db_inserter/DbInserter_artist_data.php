<?php

require_once ('db_api/db_string_utils.php');
require_once ('db_api/CountryLookup.php');
require_once ('db_api/GenreLookup.php');
require_once ('db_api/ArtistDataMySql.php');
require_once ('redis_api/ArtistDataRedis.php');

require_once ('db_manip/MusicDatabaseFactory.php');


class DbInserter_artist_data 
{

    public  function    __construct( $dbAll )
    {
        $this->m_dbAll = $dbAll;
    }

 
    public function insertToDB ( $aData )
    {
        $alias_name             = '';
        $artist_name            = trim($aData['artist_name']);
        if ( $artist_name == '' )   return;     // No artist_name. Just bail out!
        
        // The artist_name is in our Various Artist table - then we do not need to import it - Just bail out.
        if (count($this->m_dbAll->m_dbArtistVariousData->getVariousArtist($artist_name)) > 0) {
            return;
        }
        
        $artist_id = $this->m_dbAll->m_dbArtistData->lookupID( $artist_name );

        // Try reversed lookup for two word names with comma between the words (ex. 'Turner, Tina')
        if ( $artist_id <= 0 ) {
            $artist_name_reversed = reverseArtistNameWithComma($artist_name);
            if ( $artist_name_reversed != $artist_name ) {
                $artist_id = $this->m_dbAll->m_dbArtistData->nameToID( $artist_name_reversed );
                if ( $artist_id > 0 ) {
                    $alias_name = $artist_name;
                }
            }
        }
        
        // Try reversed lookup for two word names without comma (ex. 'Turner Tina')
        if ( $artist_id <= 0 ) {
            $artist_name_reversed = reverseArtistName($artist_name);
            if ( $artist_name_reversed != $artist_name ) {
                $artist_id = $this->m_dbAll->m_dbArtistData->nameToID( $artist_name_reversed );
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
    
        if ( $alias_name != '' ) {
            if ( ! $this->m_dbAll->m_dbArtistData->aliasExists ($artist_id, $alias_name) ) {
                $this->m_dbAll->m_dbArtistData->aliasCreateNew ($artist_id, $alias_name);
            }
        }
        
        $aData['artist_id']         = $artist_id;
		$aData['country_id']        = (int)$this->m_dbAll->m_dbCountryLookup->lookupID($aData['country_name']);
		$aData['artist_genre_id']   = (int)$this->m_dbAll->m_dbGenreLookup->lookupID($aData['artist_genre_name']);
        
        if ( 0 == $aData['artist_genre_id'] ) {
            $aData['record_store_id'] = $this->m_dbAll->m_dbRecordStore->nameToIDSimple($aData['record_store_name']);
            $this->m_dbAll->m_dbGenreLookup->latestUnknownSave ($aData);
        }

        
        // TODO - artist data - info
       if ( $artist_id == 0 ) {
            $gender = (string)$aData['gender']; 
            $artist_real_name = (string)$aData['artist_name'];
            $url_artist_official = (string)$aData['url_artist_official'];
            $url_fanpage = (string)$aData['url_fanpage'];
            $url_discogs = (string)$aData['url_discogs'];
            $url_musicbrainz = (string)$aData['url_musicbrainz'];
            $url_wikipedia = (string)$aData['url_wikipedia'];
            $url_allmusic = (string)$aData['url_allmusic'];
            $url_facebook = (string)$aData['url_facebook'];
            $artist_type = (string)$aData['artist_type'];
            $year_born = (int)$aData['year_born'];
            $year_died = (int)$aData['year_died'];
            $year_start = (int)$aData['year_start'];
            $year_end = (int)$aData['year_end'];
            $google_score = (int)$aData['google_score'];
            $bing_score = (int)$aData['bing_score'];
            $artist_id = $this->m_dbAll->m_dbArtistData->createNewFull($aData['artist_name'], $aData['artist_genre_id'], $aData['country_id'], $gender, $artist_real_name, $url_artist_official, $url_fanpage, $url_wikipedia, $url_allmusic, $url_musicbrainz, $url_discogs, $artist_type, $year_born, $year_died, $year_start, $year_end, $google_score, $bing_score, $url_facebook);
        } else {
            // Make 100% sure that we have the official AP artist_name in aData before updating
            $aData['artist_name']       = $this->m_dbAll->m_dbArtistData->IDToName($artist_id);
            $aData['artist_soundex']    = calcSoundex($aData['artist_name']); // Make sure we update soundex
            $this->m_dbAll->m_dbArtistData->updateBaseDataCheckOld($aData);
        }
    }

    
    // ----------------------------
    // --- PRIVATE: Member data --- 
    // ----------------------------
    private         $m_dbAll = null;
}


?>