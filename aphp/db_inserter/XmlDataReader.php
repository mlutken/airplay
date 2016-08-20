<?php

require_once ( 'db_inserter/DatabaseInserterFactory.php' );
require_once ( 'db_manip/AllDbTables.php' );

class XmlDataReader 
{
    public  function    __construct( $dbPDO = null, $redis = null )
    {
        $this->m_dbAll = new AllDbTables( $dbPDO, $redis );
        $this->m_dbInserterFactory = new DatabaseInserterFactory ( $this->m_dbAll );
    }


    public function parseOneTitle( $sRecordType, $sTitleXML )
    {
        //printf ( "--- parseOneTitle ---:\n'%s'\n", $sTitleXML );
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
        else {
            printf("ERROR XML:\n%s\n", $sTitleXML );
        }
        return $aTitle;
    }
    
    public function addTitleToDB( $aTitle )
    {
        $recordSplitter = $this->m_dbInserterFactory->createRecordSplitter( $aTitle['data_record_type'] );
        $aRecordsToInsert = $recordSplitter->splitRecord( $aTitle );
        //var_dump($aRecordsToInsert);
        foreach ( $aRecordsToInsert as $aDataRecord ) {
            $dbInserter = $this->m_dbInserterFactory->createInserter( $aDataRecord['data_record_type'] );
            if ( $dbInserter != null ) {
                $dbInserter->insertToDB( $aDataRecord );
                //var_dump( $aDataRecord );
            }
        }
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
                if ( strpos($sLine, "<dbg_" ) === false ) {
                    $this->m_sTitleXML .= $sLine;
                }
                
                if ( strpos( "_SKIP_TITLE_", $sLine) != -1 ) $this->m_bSkipTitle;
            }
        }
        fclose($fp);
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
    private         $m_dbAll = null;
    private         $m_dbPDO = null;
    private         $m_redis = null;
    private         $m_sTitleXML;
    private         $m_bInTitle;
    private         $m_iTitlesCounter;
    private         $m_iMaxRecordsToRead;
    private         $m_dbInserterFactory;
    private         $m_bShowProgress = false;
    
}


?>