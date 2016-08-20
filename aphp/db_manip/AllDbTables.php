<?php


require_once ('db_manip/MusicDatabaseFactory.php');


class AllDbTables
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public  function    __construct( $dbPDO = null, $redis = null )
    {
        $fac = new MusicDatabaseFactory($dbPDO, $redis);
        $this->m_dbAllArtists   		= $fac->createDbInterface("AllArtistsData");    // TODO: Out, not used anymore
        $this->m_dbAllItemBases   		= $fac->createDbInterface("AllItemBasesData");  // TODO: Out, not used anymore
        $this->m_dbItemBaseAlias   		= $fac->createDbInterface("ItemBaseAliasData"); // TODO: Out, not used anymore
        
        $this->m_dbArtistAlias          = $fac->createDbInterface("ArtistAliasData");
        $this->m_dbArtistData           = $fac->createDbInterface("ArtistData");
        $this->m_dbArtistVariousData    = $fac->createDbInterface("ArtistVariousData");
        $this->m_dbFavoriteArtistData   = $fac->createDbInterface("FavoriteArtistData");
        $this->m_dbFriendsData          = $fac->createDbInterface("FriendsData");
        $this->m_dbItemBaseCorrection   = $fac->createDbInterface("ItemBaseCorrectionData");
        $this->m_dbRecordStore          = $fac->createDbInterface("RecordStoreData");
        $this->m_MediaFormatLookup      = $fac->createDbInterface("MediaFormatLookup");
        $this->m_MediaTypeLookup        = $fac->createDbInterface("MediaTypeLookup");
        $this->m_dbArtistAliasData      = $fac->createDbInterface("ArtistAliasData");
        $this->m_dbItemBaseData         = $fac->createDbInterface("ItemBaseData");
        $this->m_dbItemBaseReviewData   = $fac->createDbInterface("ItemBaseReviewData");
        $this->m_dbSettingsData   		= $fac->createDbInterface("SettingsData");
        $this->m_dbGenreLookup          = $fac->createDbInterface("GenreLookup");
        $this->m_dbItemPriceData        = $fac->createDbInterface("ItemPriceData");
        $this->m_dbCurrencyData         = $fac->createDbInterface("CurrencyData");
        $this->m_dbCountryLookup        = $fac->createDbInterface("CountryLookup");
        $this->m_dbQuizData             = $fac->createDbInterface("QuizData");
        $this->m_dbQuizScoreData        = $fac->createDbInterface("QuizScoreData");
        $this->m_dbQuizThemeData        = $fac->createDbInterface("QuizThemeData");
        $this->m_dbUserData             = $fac->createDbInterface("UserData");

    }

    
    // --------------------
    // --- PUBLIC: Data --- 
    // --------------------
	public          $m_dbAllArtists;
	public          $m_dbAllItemBases;
	public          $m_dbArtistAlias;
	public          $m_dbItemBaseAlias;

    public          $m_dbItemBaseCorrection;
    public          $m_dbRecordStore;
    public          $m_MediaFormatLookup;
    public          $m_MediaTypeLookup;
    public			$m_dbArtistAliasData;
    public          $m_dbArtistData;
    public          $m_dbArtistVariousData;
    public          $m_dbFavoriteArtistData;
    public          $m_dbFriendsData;
    public          $m_dbItemBaseData;
    public          $m_dbItemBaseReviewData;
    public          $m_dbGenreLookup;
    public          $m_dbItemPriceData;
    public          $m_dbCurrencyData;
    public          $m_dbCountryLookup;
    public          $m_dbQuizData;
    public          $m_dbQuizScoreData;
    public          $m_dbQuizThemeData;
    public          $m_dbUserData;
   
}

/** All DB tables inclusive the fast lookups like CurrencyConvert, ArtistAliasLookup, ItemBaseCorrectionLookup 
*/
class AllDbTablesWithLookup extends AllDbTables
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $iModuloBase, $iModuloMatch, $dbPDO = null, $redis = null )
    {
        parent::__construct( $dbPDO, $redis );
        $fac = new MusicDatabaseFactory($dbPDO, $redis);
        $this->m_CurrencyConvert    	= $fac->createDbInterface("CurrencyConvert");
        $this->m_dbItemBaseAliasLookup 	= $fac->createDbInterface("ItemBaseAliasLookup");
        
        // Only read in aliases that match out current modolo parameters
        $this->m_dbArtistAliasLookup 	= new ArtistAliasLookup(null);
        $this->m_dbArtistAliasLookup->moduloInitialiseFromTableDb( $this->m_dbArtistAlias, $iModuloBase, $iModuloMatch );
    }

    

    // --------------------
    // --- PUBLIC: Data --- 
    // --------------------
    public		$m_CurrencyConvert;
	public      $m_dbArtistAliasLookup;
	public      $m_dbItemBaseAliasLookup;
    
}

?>