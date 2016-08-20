<?php

require_once ( 'new_db_inserter/DatabaseInserterFactory.php' );
require_once ( 'db_manip/AllDbTables.php' );
require_once ( 'filedb_api/OpenParentsMgr.php');

class XmlDataReader 
{
	
    public  function    __construct( $fileDbBaseDir, $dbPDO, $redis )
    {
        $this->m_fileDbBaseDir = $fileDbBaseDir;
    }

    public function moduloBaseSet	( $iModuloBase )	{	$this->m_iModuloBase = $iModuloBase;  }
    public function moduloMatchSet	( $iModuloMatch )	{	$this->m_iModuloMatch = $iModuloMatch;  }

    public function init()
    {
        $this->m_dbAll = new AllDbTablesWithLookup( $this->m_iModuloBase, $this->m_iModuloMatch, $dbPDO, $redis );
        $this->m_openParents = new OpenParentsMgr($fileDbBaseDir, $this->m_dbAll);
        $this->m_dbInserterFactory = new DatabaseInserterFactory ( $this->m_fileDbBaseDir, $this->m_dbAll, $this->m_openParents );

        $this->m_RecordStoreInserter    =  $this->m_dbInserterFactory->createInserter('record_store');
        $this->m_ArtistInserter         =  $this->m_dbInserterFactory->createInserter('artist_data');
        $this->m_ItemBaseInserter       =  $this->m_dbInserterFactory->createInserter('item_base');
        $this->m_ItemPriceInserter      =  $this->m_dbInserterFactory->createInserter('item_price');
        
        // --- Initialise modulo settings ---
        $this->m_RecordStoreInserter->moduloBaseSet($this->m_iModuloBase);
        $this->m_RecordStoreInserter->moduloMatchSet($this->m_iModuloMatch);
        $this->m_ArtistInserter->moduloBaseSet($this->m_iModuloBase);
        $this->m_ArtistInserter->moduloMatchSet($this->m_iModuloMatch);
        $this->m_ItemBaseInserter->moduloBaseSet($this->m_iModuloBase);
        $this->m_ItemBaseInserter->moduloMatchSet($this->m_iModuloMatch);
        $this->m_ItemPriceInserter->moduloBaseSet($this->m_iModuloBase);
        $this->m_ItemPriceInserter->moduloMatchSet($this->m_iModuloMatch);
        
		$this->m_aInserters = array (
				'artist_data'      =>  $this->m_ArtistInserter,
				'album_base'       =>  $this->m_ItemBaseInserter,
				'song_base'        =>  $this->m_ItemBaseInserter,
				'merchandise_base' =>  $this->m_ItemBaseInserter,
				'album'            =>  $this->m_ItemPriceInserter,
				'song'             =>  $this->m_ItemPriceInserter,
				'merchandise'      =>  $this->m_ItemPriceInserter,
                'concert'          =>  $this->m_ItemPriceInserter,
				'record_store'     =>  $this->m_RecordStoreInserter
		);
    
    }
    
    public function parseOneTitle( $sRecordType, $sTitleXML )
    {
//         printf ( "--- parseOneTitle ---:\n'%s'\n", $sTitleXML );
        $aTitle = array(); 
        $xml = new DOMDocument();
        if ( $xml->loadXML( $sTitleXML ) ) {
            $titleNodeList = $xml->getElementsByTagName($sRecordType);
            if ( count($titleNodeList) != 1 ) return null;
            
            $locNode = $titleNodeList->item(0);
            $elemList = $locNode->getElementsByTagName("*");
            if ( $elemList->length == 0 ) return null;
            
            foreach ( $elemList as $elem ) 
            {
                $aTitle[ $elem->nodeName ] = $elem->nodeValue;
            }
        }
        return $aTitle;
    }
    
    public function addTitleToDB( $aTitle )
    {
		$inserter = $this->m_aInserters[ $aTitle['data_record_type'] ];
		if ( '' == $inserter ) return;
		
		$inserter->insertToDB ( $aTitle );
		
//  		var_dump( $aTitle );
//         $recordSplitter = $this->m_dbInserterFactory->createRecordSplitter( $aTitle['data_record_type'] );
//         $aRecordsToInsert = $recordSplitter->splitRecord( $aTitle );
//         //var_dump($aRecordsToInsert);
//         foreach ( $aRecordsToInsert as $aDataRecord ) {
//             $dbInserter = $this->m_dbInserterFactory->createInserter( $aDataRecord['data_record_type'] );
//             if ( $dbInserter != null ) {
//                 $dbInserter->insertToDB( $aTitle );
//                 //var_dump( $aDataRecord );
//             }
//         }
    }

    
    public  function    beginTitle($sRecordType)
    {
        $this->m_bInTitle = true;
        $this->m_bSkipTitle = false;
        $this->m_sTitleXML = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<ROOT>\n<$sRecordType>\n";
    }

    public  function    endTitle($sRecordType)
    {
        $this->m_bInTitle = false;
        $this->m_sTitleXML .= "</$sRecordType>\n</ROOT>\n";
        $this->m_iTitlesCounter++;
    }
    
     
    public  function readXMLData( $sFileName )
    {
        $sRecordType = 'title';
        printf( "readXMLData: $sFileName, $sRecordType\n" );
        $this->m_bInTitle       = false;
        $this->m_iTitlesCounter = 0;
        $fp = fopen( $sFileName, 'r');
        if ( !$fp ) {
            printf("ERROR openeing $sFileName\n");
            exit(1);
        }
        
        $bInTitle = false;
        
        while ( !feof($fp) ) {
            $sLine = fgets( $fp );  // Read next line from the file
            $i = strpos( $sLine, "<$sRecordType>" );
            
            if ( strpos($sLine, "<$sRecordType>" ) !== false ) {
                $this->beginTitle($sRecordType);
                continue;
            }
            if ( strpos($sLine, "</$sRecordType>" ) !== false ) {
                $this->endTitle($sRecordType);
                
                //printf ( "--- title ---:\n'%s'\n", $this->m_sTitleXML );
                $aTitle = $this->parseOneTitle( $sRecordType, $this->m_sTitleXML );
                $this->addTitleToDB( $aTitle );
                if ( $this->m_bShowProgress && ($this->m_iTitlesCounter % 100 == 0) ) {
                    printf("Progress[%d]: '%s'\n", $this->m_iTitlesCounter, $aTitle['artist_name']);
                }
                
                
                if ( $this->m_iMaxRecordsToRead > -1 && $this->m_iTitlesCounter >= $this->m_iMaxRecordsToRead ) {
                    break;
                }
                continue;
            }
            else if ( $this->m_bInTitle ) {
                $this->m_sTitleXML .= $sLine;
                if ( strpos( "_SKIP_TITLE_", $sLine) != -1 ) $this->m_bSkipTitle;
            }
        }
        fclose($fp);
        $this->m_openParents->writeAll();	// Write any open parent items (i.e. artists) to disk
        printf ("\n*** Adding last data ***");
        ////$this->parseOneTitle( $sRecordType, $sTitleXML); 
        printf ( "\nTitles in total: {$this->m_iTitlesCounter}\n" );
    }

    
    public function maxRecordsToRead( $iMaxRecordsToRead )
    {
        $this->m_iMaxRecordsToRead = $iMaxRecordsToRead;
    }
    
    
    public function showProgress( $bShowProgress )
    {
        $this->m_bShowProgress = $bShowProgress;
    }
    
    // -----------------------
    // --- Debug functions ---
    // -----------------------
    public  function    dbgPrintTitles()
    {
        print "\n*** Print titles *** \n";
    }

    public function dbgGetTestTitle()
    {
        return "
        <title>
            <record_store_name>Megastore.Se</record_store_name>
            <record_store_url>http://www.megastore.se</record_store_url>
            <buy_at_url>http://www.megastore.se/template/next%2CProduct.vm?itemid=980808</buy_at_url>
            <artist_name><![CDATA[!!!]]></artist_name>
            <album_name>Louden Up Now</album_name>
            <data_record_type>album</data_record_type>
            <price_local>159</price_local>
            <currency_name>SEK</currency_name>
        </title>
        ";
    }

    // ----------------------------
    // --- PRIVATE: Member data --- 
    // ----------------------------
    private         $m_fileDbBaseDir;
    private         $m_dbAll = null;
    private			$m_openParents;
    // --- Modulo stuff. Default values are set so we handle all - that is same as no modulo splitting of the data handling
    private			$m_iModuloBase 	= 1;	// Main modulo number which we divide with. For example: 4
    private			$m_iModuloMatch = 0;	// The 'remainder' that we match after dividing with m_iModuloBase. If m_iModuloBase = 4 then m_iModuloMatch is one of 0,1,2,3 
    
// //     private         $m_dbPDO = null;
// //     private         $m_redis = null;
    private         $m_sTitleXML;
    private         $m_bInTitle;
    private         $m_iTitlesCounter;
    private         $m_iMaxRecordsToRead;
    private         $m_dbInserterFactory;
    private         $m_bShowProgress = false;
    
    private         $m_aInserters = array();
    
}


?>