<?php

require_once ('db_api/MediaFormatLookup.php');
require_once ('db_api/MediaTypeLookup.php');
require_once ('db_api/GenreLookup.php');
require_once ('db_api/CountryLookup.php');
require_once ('db_api/ItemTypeConvert.php');


require_once ('db_api/RecordStoreDataMySql.php');
require_once ('db_api/ArtistDataMySql.php');
require_once ('db_api/AllArtistsDataMySql.php');
require_once ('db_api/AllItemBasesDataMySql.php');
require_once ('db_api/ArtistAliasDataMySql.php');
require_once ('db_api/ArtistAliasLookup.php');
require_once ('db_api/ArtistSynonymDataMySql.php');
require_once ('db_api/ArtistVariousDataMySql.php');
require_once ('db_api/FavoriteArtistDataMySql.php');
require_once ('db_api/FriendsDataMySql.php');
require_once ('db_api/ItemBaseAliasDataMySql.php');
require_once ('db_api/ItemBaseAliasLookup.php');
require_once ('db_api/ItemDataMySql.php');
require_once ('db_api/ItemBaseReviewDataMySql.php');
require_once ('db_api/PriceDataMySql.php');
require_once ('db_api/ItemBaseCorrectionDataMySql.php');
require_once ('db_api/CurrencyConvert.php');
require_once ('db_api/CurrencyDataMySql.php');
require_once ('db_api/CurrencyToEuroDataMySql.php');
require_once ('db_api/GenreDataMySql.php');
require_once ('db_api/JobDataMySql.php');
require_once ('db_api/MediaFormatDataMySql.php');
require_once ('db_api/MediaTypeDataMySql.php');
require_once ('db_api/QuizDataMySql.php');
require_once ('db_api/QuizScoreDataMySql.php');
require_once ('db_api/QuizThemeDataMySql.php');
require_once ('db_api/SettingsDataMySql.php');
require_once ('db_api/UnknownGenresDataMySql.php');
require_once ('db_api/UnknownMediaFormatsDataMySql.php');
require_once ('db_api/UnknownMediaTypesDataMySql.php');
require_once ('db_api/UserDataMySql.php');

require_once ('redis_api/RecordStoreDataRedis.php');
require_once ('redis_api/ArtistDataRedis.php');
require_once ('redis_api/ItemBaseDataRedis.php');
require_once ('redis_api/ItemPriceDataRedis.php');
require_once ('redis_api/ItemBaseCorrectionDataRedis.php');
// require_once ('db_api/CurrencyDataRedis.php');   // TODO: Not done yet!


class MusicDatabaseFactory
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public  function    __construct(  $dbPDO = null, $redis = null )
    {
        global $g_MySqlPDO, $g_redis, $g_useRedis;
        $this->m_dbPDO = $dbPDO;
        if ( $dbPDO == null ) $this->m_dbPDO = $g_MySqlPDO; 
        $this->m_redis = $redis;
        if ( $redis == null ) $this->m_redis = $g_redis;      
        
    }

    // ------------------------
    // --- Lookup functions --- 
    // ------------------------
    public function createDbInterface ( $interfaceName )
    {
        global $g_useRedis;
        
        if ( $g_useRedis ) {
            return $this->createRedisInterface($interfaceName);
        }
        else {
            return $this->createMySqlInterface($interfaceName);
        }
    }
    
//     new CurrencyConvert( new CurrencyDataMySql() );
    // --------------------------
    // --- PRIVATE: Functions --- 
    // --------------------------
    private function createMySqlInterface ( $interfaceName )
    {
        switch ( $interfaceName ) 
        {
            case 'AllArtistsData'           :   return new AllArtistsDataMySql          ( $this->m_dbPDO );
            case 'AllItemBasesData'         :   return new AllItemBasesDataMySql        ( $this->m_dbPDO );
            case 'ArtistData'               :   return new ArtistDataMySql              ( $this->m_dbPDO );
            case 'ArtistAliasData'        	:   return new ArtistAliasDataMySql       	( $this->m_dbPDO );
            case 'ArtistAliasLookup'        :   return new ArtistAliasLookup			( new ArtistAliasDataMySql($this->m_dbPDO) );
            case 'ArtistSynonymData'        :   return new ArtistSynonymDataMySql       ( $this->m_dbPDO );
            case 'ArtistVariousData'        :   return new ArtistVariousDataMySql       ( $this->m_dbPDO );
            case 'CountryLookup'            :   return new CountryLookup                ( $this->m_dbPDO );
            case 'CurrencyConvert'          :   return new CurrencyConvert				( new CurrencyDataMySql($this->m_dbPDO) );
            case 'CurrencyData'             :   return new CurrencyDataMySql            ( $this->m_dbPDO );
            case 'CurrencyToEuroData'       :   return new CurrencyToEuroDataMySql      ( $this->m_dbPDO );
            case 'FavoriteArtistData'       :   return new FavoriteArtistDataMySql      ( $this->m_dbPDO );
            case 'FriendsData'              :   return new FriendsDataMySql             ( $this->m_dbPDO );
            case 'ItemBaseCorrectionData'   :   return new ItemBaseCorrectionDataMySql  ( $this->m_dbPDO );
            case 'ItemTypeConvert'          :   return new ItemTypeConvert              ();
            case 'RecordStoreData'          :   return new RecordStoreDataMySql         ( $this->m_dbPDO );
            case 'MediaFormatLookup'        :   return new MediaFormatLookup            ( $this->m_dbPDO );
            case 'MediaTypeLookup'          :   return new MediaTypeLookup              ( $this->m_dbPDO );
            case 'ItemBaseAliasData'        :   return new ItemBaseAliasDataMySql       ( $this->m_dbPDO );
            case 'ItemBaseAliasLookup'      :   return new ItemBaseAliasLookup			( new ItemBaseAliasDataMySql($this->m_dbPDO) );
            case 'ItemBaseData'             :   return new ItemDataMySql                ( $this->m_dbPDO );
            case 'ItemBaseReviewData'       :   return new ItemBaseReviewDataMySql 		( $this->m_dbPDO );
            case 'GenreLookup'              :   return new GenreLookup                  ( $this->m_dbPDO );
            case 'ItemPriceData'            :   return new PriceDataMySql               ( $this->m_dbPDO );
            case 'GenreData'                :   return new GenreDataMySql               ( $this->m_dbPDO );
            case 'JobData'                	:   return new JobDataMySql               	( $this->m_dbPDO );
            case 'MediaFormatData'          :   return new MediaFormatDataMySql         ( $this->m_dbPDO );
//             case 'MediaTypeData'            :   return new MediaTypeDataMySql           ( $this->m_dbPDO );
            case 'QuizData'        			:   return new QuizDataMySql       			( $this->m_dbPDO );
            case 'QuizScoreData'            :   return new QuizScoreDataMySql           ( $this->m_dbPDO );
            case 'QuizThemeData'            :   return new QuizThemeDataMySql           ( $this->m_dbPDO );
            case 'SettingsData'        		:   return new SettingsDataMySql       		( $this->m_dbPDO );
            case 'UnknownGenresData'        :   return new UnknownGenresDataMySql       ( $this->m_dbPDO );
            case 'UnknownMediaFormatsData'  :   return new UnknownMediaFormatsDataMySql ( $this->m_dbPDO );
            case 'UnknownMediaTypesData'    :   return new UnknownMediaTypesDataMySql   ( $this->m_dbPDO );
            case 'UserData'                 :   return new UserDataMySql                ( $this->m_dbPDO );
            default                         :   return null;
        }
    }
 
 
    private function createRedisInterface ( $interfaceName )
    {
        switch ( $interfaceName ) 
        {
            case 'AllArtistsData'           :   return new AllArtistsDataMySql          ( $this->m_dbPDO );
            case 'AllItemBasesData'         :   return new AllItemBasesDataMySql        ( $this->m_dbPDO );
            case 'ArtistData'               :   return new ArtistDataRedis              ( $this->m_redis );
            case 'ArtistAliasData'        	:   return new ArtistAliasDataMySql       	( $this->m_dbPDO );
            case 'ArtistAliasLookup'        :   return new ArtistAliasLookup			( new ArtistAliasDataMySql($this->m_dbPDO) );
            case 'ArtistSynonymData'        :   return new ArtistSynonymDataMySql       ( $this->m_dbPDO );
            case 'ArtistVariousData'        :   return new ArtistVariousDataMySql       ( $this->m_dbPDO );
            case 'CountryLookup'            :   return new CountryLookup                ( $this->m_dbPDO );
            case 'CurrencyConvert'          :   return new CurrencyConvert				( new CurrencyDataMySql($this->m_dbPDO) );
            case 'CurrencyData'             :   return new CurrencyDataMySql            ( $this->m_dbPDO );
            case 'CurrencyToEuroData'       :   return new CurrencyToEuroDataMySql      ( $this->m_dbPDO );
            case 'FavoriteArtistData'       :   return new FavoriteArtistDataMySql      ( $this->m_dbPDO );
            case 'FriendsData'              :   return new FriendsDataMySql             ( $this->m_dbPDO );
            case 'ItemBaseCorrectionData'   :   return new ItemBaseCorrectionDataRedis  ( $this->m_redis );
            case 'ItemTypeConvert'          :   return new ItemTypeConvert              ();
            case 'RecordStoreData'          :   return new RecordStoreDataRedis         ( $this->m_redis );
            case 'MediaFormatLookup'        :   return new MediaFormatLookup            ( $this->m_dbPDO );
            case 'MediaTypeLookup'          :   return new MediaTypeLookup              ( $this->m_dbPDO );
            case 'ItemBaseAliasData'        :   return new ItemBaseAliasDataMySql       ( $this->m_dbPDO );
            case 'ItemBaseAliasLookup'      :   return new ItemBaseAliasLookup			( new ItemBaseAliasDataMySql($this->m_dbPDO) );
            case 'ItemBaseData'             :   return new ItemBaseDataRedis            ( $this->m_redis );
            case 'ItemBaseReviewData'       :   return new ItemBaseReviewDataMySql 		( $this->m_dbPDO );
            case 'GenreLookup'              :   return new GenreLookup                  ( $this->m_dbPDO );
            case 'ItemPriceData'            :   return new ItemPriceDataRedis           ( $this->m_redis );
            case 'GenreData'                :   return new GenreDataMySql               ( $this->m_dbPDO );
            case 'JobData'                	:   return new JobDataMySql               	( $this->m_dbPDO );
            case 'MediaFormatData'          :   return new MediaFormatDataMySql         ( $this->m_dbPDO );
//             case 'MediaTypeData'            :   return new MediaTypeDataMySql           ( $this->m_dbPDO );
            case 'QuizData'        			:   return new QuizDataMySql       			( $this->m_dbPDO );
            case 'QuizScoreData'            :   return new QuizScoreDataMySql           ( $this->m_dbPDO );
            case 'QuizThemeData'            :   return new QuizThemeDataMySql           ( $this->m_dbPDO );
            case 'SettingsData'        		:   return new SettingsDataMySql       		( $this->m_dbPDO );
            case 'UnknownGenresData'        :   return new UnknownGenresDataMySql       ( $this->m_dbPDO );
            case 'UnknownMediaFormatsData'  :   return new UnknownMediaFormatsDataMySql ( $this->m_dbPDO );
            case 'UnknownMediaTypesData'    :   return new UnknownMediaTypesDataMySql   ( $this->m_dbPDO );
            case 'UserData'                 :   return new UserDataMySql                ( $this->m_dbPDO );
            default                         :   return null;
        }
    }

    // ---------------------
    // --- PRIVATE: Data --- 
    // ---------------------
    private         $m_dbPDO = null;
    private         $m_redis = null;
    
    
}


?>