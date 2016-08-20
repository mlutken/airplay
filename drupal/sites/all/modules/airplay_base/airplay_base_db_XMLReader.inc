<?php


class AirplayXMLReader 
{

	public 	function	__construct( $bDoAddData = false, $bInsUpdDefault = false )
	{
		$this->m_bDoAddData			= $bDoAddData;
		$this->m_bInsUpdDefault		= $bInsUpdDefault;	// Force insert,update in the default set of tables. See airplay_base_db_DefaultTables.inc
		$this->m_iChunkSize 		= (int)2500;
		$this->m_bInTitle 			= false;
		$this->m_aRecords 			= array();
	}

	public	function	parseOneRecord( $sTitleXML, $sRecordType )
	{
		switch ( $sRecordType )
		{
		case  "title" : 
			$this->parseOneTitle( $sTitleXML );
		break;
		case  "info_artist" : 
			$this->parseOneInfoArtist( $sTitleXML );
		}

	}

	public function parseCommon( $sRecordType, $sTitleXML, $aDefault__do_insert, $aDefault__do_update, $aDefault__do_update_on_empty )
	{
		$aTitle = null;	
		$xml = new DOMDocument();
		if ( $xml->loadXML( $sTitleXML ) ) {
			$titleNodeList = $xml->getElementsByTagName($sRecordType);
			if ( count($titleNodeList) != 1 ) return null;
			
			$locNode = $titleNodeList->item(0);
			$elemList = $locNode->getElementsByTagName("*");
 			if ( $elemList->length == 0 ) return null;

 			
			$do_insert__FOUND =	false;
			$do_update__FOUND =	false;
			$do_update_on_empty__FOUND =	 false;
			$aTitle = null;	
			foreach ( $elemList as $elem ) 
			{
				if ( $elem->nodeName == 'do_insert' ) {
					$a = explode ( ',' , $elem->nodeValue );
					if ( count($a) > 0 ) $do_insert__FOUND = true;
					array_walk ( $a, 'trim_value' );
					$aTitle['do_insert'] = $a; 
				}
				else if ( $elem->nodeName == 'do_update' ) {
					$aTbls = explode ( ';' , $elem->nodeValue );
					if ( count($aTbls) > 0 ) $do_update__FOUND = true;
					foreach ( $aTbls as $sTbl ) {
						$aPair = explode ( ':' , $sTbl);
						$sTblName = trim ($aPair[0]);
						$aUpdElems = explode ( ',' , $aPair[1]);
						array_walk ( $aUpdElems, 'trim_value' );
						$aTitle['do_update'][$sTblName] = $aUpdElems; 
					}
				}
				else if ( $elem->nodeName == 'do_update_on_empty' ) {
					$aTbls = explode ( ';' , $elem->nodeValue );
					if ( count($aTbls) > 0 ) $do_update_on_empty__FOUND = true;
					foreach ( $aTbls as $sTbl ) {
						$aPair = explode ( ':' , $sTbl);
						$sTblName = trim ($aPair[0]);
						$aUpdElems = explode ( ',' , $aPair[1]);
						array_walk ( $aUpdElems, 'trim_value' );
						$aTitle['do_update_on_empty'][$sTblName] = $aUpdElems; 
					}
				}
				else {
					$aTitle[ $elem->nodeName ] = $elem->nodeValue;
				}
			}

			
			if ( !$do_insert__FOUND || $this->m_bInsUpdDefault ) {
				$aTitle['do_insert'] 			= $aDefault__do_insert;
			}
			if ( !$do_update__FOUND || $this->m_bInsUpdDefault ) {
				$aTitle['do_update'] 			= $aDefault__do_update;
			}
			if ( !$do_update_on_empty__FOUND || $this->m_bInsUpdDefault ) {
				$aTitle['do_update_on_empty'] 	= $aDefault__do_update_on_empty;
			}
			$xml = null;
		}
	
	
		return $aTitle;
	}
	
	
	public	function	parseOneInfoArtist( $sTitleXML  )
	{
	
	
		$aDefault__do_insert 			= array ( 'artist', 'info_artist', 'artist_synonym', 'subgenre' );
		$aDefault__do_update 			= array
				( 
					'artist' => array( 'genre_id', 'country_id' ),
					'info_artist' => array( "*" )
				);  					
		$aDefault__do_update_on_empty 	=  array
				( 
					'artist' => array(),
					'info_artist' => array()
				);  
	
	
		$aTitle = $this->parseCommon( "info_artist", $sTitleXML, $aDefault__do_insert, $aDefault__do_update, $aDefault__do_update_on_empty );
// 		$xml = new DOMDocument();
// 		if ( $xml->loadXML( $sTitleXML ) ) {
// 			$titleNodeList = $xml->getElementsByTagName("info_artist");
// 			if ( count($titleNodeList) != 1 ) return;
// 			
// 			$locNode = $titleNodeList->item(0);
// 			$elemList = $locNode->getElementsByTagName("*");
//  			if ( $elemList->length == 0 ) return;
// 				
// 			$aTitle = null;	
// 			foreach ( $elemList as $elem ) 
// 			{
// 				$aTitle[ $elem->nodeName ] = $elem->nodeValue;
// 			}
// // 				drush_print ( "['{$elem->nodeName}'] = '{$elem->nodeValue}'" );
// 			$aTitle['do_insert'] 			= array ( 'artist', 'info_artist', 'artist_synonym', 'subgenre' );
// 			$aTitle['do_update'] 			= array
// 					( 
// 						'artist' => array( 'genre_id', 'country_id' ),
// 						'info_artist' => array( "*" )
// 					);  					
// 			$aTitle['do_update_on_empty'] 	=  array
// 					( 
// 						'artist' => array(),
// 						'info_artist' => array()
// 					);  
// 		}
		
		if ( $aTitle != null ) {
			$bDoAdd = true;
			$data_record_type = $aTitle['data_record_type'];
			
			
			if ( $aTitle['skip_title'] == '1' ) {
                $bDoAdd = false;
            }
            else if ( ap_ends_with( $aTitle["artist_name"], "..." ) ) {
                $bDoAdd = false;
            }
            else {
                foreach ( $aTitle as $key => $val ) {
                    if ( $val == "_SKIP_TITLE_" ) {
                        $bDoAdd = false;
                    }
                }
            }
			if ( $bDoAdd ) {
				$this->m_iTitlesCounter++;
				$this->m_aRecords[] = $aTitle;
				//var_dump ($aTitle);
			}
			else {
                //printf("Skipping: '%s'\n", $aTitle["{$data_record_type}_name"] );
			}
			$xml = null;
		}
        // --------------------------------------------------------------------
        // --- If we have a good chunk (like 10.000 titles add them to DB ) ---
        // --------------------------------------------------------------------
        if ( (int)$this->m_iTitlesCounter % $this->m_iChunkSize == 0 ) {
            $this->addData('info_artist'); 
        }
	}
	
	public	function	parseOneTitle( $sTitleXML  )
	{
		$xml = new DOMDocument();
		if ( $xml->loadXML( $sTitleXML ) ) {
			$titleNodeList = $xml->getElementsByTagName("title");
			if ( count($titleNodeList) != 1 ) return;
			
			$locNode = $titleNodeList->item(0);
			$elemList = $locNode->getElementsByTagName("*");
 			if ( $elemList->length == 0 ) return;
				
			$do_insert__FOUND =	false;
			$do_update__FOUND =	false;
			$do_update_on_empty__FOUND =	 false;
			$aTitle = null;	
			foreach ( $elemList as $elem ) 
			{
				if ( $elem->nodeName == 'do_insert' ) {
					$a = explode ( ',' , $elem->nodeValue );
					if ( count($a) > 0 ) $do_insert__FOUND = true;
					array_walk ( $a, 'trim_value' );
					$aTitle['do_insert'] = $a; 
				}
				else if ( $elem->nodeName == 'do_update' ) {
					$aTbls = explode ( ';' , $elem->nodeValue );
					if ( count($aTbls) > 0 ) $do_update__FOUND = true;
					foreach ( $aTbls as $sTbl ) {
						$aPair = explode ( ':' , $sTbl);
						$sTblName = trim ($aPair[0]);
						$aUpdElems = explode ( ',' , $aPair[1]);
						array_walk ( $aUpdElems, 'trim_value' );
						$aTitle['do_update'][$sTblName] = $aUpdElems; 
					}
				}
				else if ( $elem->nodeName == 'do_update_on_empty' ) {
					$aTbls = explode ( ';' , $elem->nodeValue );
					if ( count($aTbls) > 0 ) $do_update_on_empty__FOUND = true;
					foreach ( $aTbls as $sTbl ) {
						$aPair = explode ( ':' , $sTbl);
						$sTblName = trim ($aPair[0]);
						$aUpdElems = explode ( ',' , $aPair[1]);
						array_walk ( $aUpdElems, 'trim_value' );
						$aTitle['do_update_on_empty'][$sTblName] = $aUpdElems; 
					}
				}
				else {
					$aTitle[ $elem->nodeName ] = $elem->nodeValue;
				}
// 				drush_print ( "['{$elem->nodeName}'] = '{$elem->nodeValue}'" );
			}
			if ( $this->m_bInsUpdDefault ) {
				if 		( $aTitle['data_record_type'] == 'record_store' ) 	{
					$aTitle['do_insert'] 			= DefaultTables::I()->getInsertList__record_store();
					$aTitle['do_update'] 			= DefaultTables::I()->getUpdateList__record_store();
					$aTitle['do_update_on_empty'] 	= DefaultTables::I()->getUpdateOnEmptyList__record_store();
				}
				else if ( $aTitle['data_record_type'] == 'artist' ) {
					$aTitle['do_insert'] 			= DefaultTables::I()->getInsertList__artist();
					$aTitle['do_update'] 			= DefaultTables::I()->getUpdateList__artist();
					$aTitle['do_update_on_empty'] 	= DefaultTables::I()->getUpdateOnEmptyList__artist();
				}
				else if ( $aTitle['data_record_type'] == 'song' ) 	{
					$aTitle['do_insert'] 			= DefaultTables::I()->getInsertList__song();
					$aTitle['do_update'] 			= DefaultTables::I()->getUpdateList__song();
					$aTitle['do_update_on_empty'] 	= DefaultTables::I()->getUpdateOnEmptyList__song();
				}
				else if ( $aTitle['data_record_type'] == 'album' )	{
					$aTitle['do_insert'] 			= DefaultTables::I()->getInsertList__album();
					$aTitle['do_update'] 			= DefaultTables::I()->getUpdateList__album();
					$aTitle['do_update_on_empty'] 	= DefaultTables::I()->getUpdateOnEmptyList__album();
				}
			}
			$bDoAdd = true;
			$data_record_type = $aTitle['data_record_type'];
			
			if ( $data_record_type == "album" || $data_record_type == "song" ) {
            // --- Skip zero and non-existant prices ---
                if ( $aTitle['price_local'] == "" || $aTitle['price_local'] == "0" || $aTitle['price_local'] <= 0 ) {
                    $bDoAdd = false;
                }
                else if ( strlen($aTitle["buy_at_url"]) < 10 ) {
                    $bDoAdd = false;
                }
                else if ( $aTitle["artist_name"] == "" ) {
                    $bDoAdd = false;
                }
                else if ( $aTitle["{$data_record_type}_name"] == "" ) {
                    $bDoAdd = false;
                }
                else if ( $aTitle["media_format_name"] == "" ) {
                    $bDoAdd = false;
                }
                else if ( $aTitle["media_type_name"] == "" ) {
                    $bDoAdd = false;
                }
                else if ( $aTitle["record_store_name"] == "" ) {
                    $bDoAdd = false;
                }
			}
            else if ( $data_record_type == "artist" ) {
                if ( $aTitle["artist_name"] == "" ) {
                    $bDoAdd = false;
                }
            }
			
			if ( $aTitle['skip_title'] == '1' ) {
                $bDoAdd = false;
            }
            else if ($data_record_type == "" ) {
                $bDoAdd = false;
            }
            else if ( ap_ends_with( $aTitle["artist_name"], "..." ) ) {
                $bDoAdd = false;
            }
            else if ( ap_ends_with( $aTitle["{$data_record_type}_name"], "..." ) ) {
                $bDoAdd = false;
            }
            else {
                foreach ( $aTitle as $key => $val ) {
                    if ( $val == "_SKIP_TITLE_" ) {
                        $bDoAdd = false;
                    }
                }
            }
			if ( $bDoAdd ) {
 				$this->m_iTitlesCounter++;
				$this->m_aRecords[] = $aTitle;
			}
			else {
                //printf("Skipping: '%s'\n", $aTitle["{$data_record_type}_name"] );
			}
            if ( $aTitle["artist_name"] == "Johnny Madsen" ) {
                printf("Adding(%d): '%s' =>'%s'\n", $bDoAdd, $aTitle["artist_name"], $aTitle["{$data_record_type}_name"] );
            }
			
			$xml = null;
		}
		
		// --------------------------------------------------------------------
		// --- If we have a good chunk (like 10.000 titles add them to DB ) ---
		// --------------------------------------------------------------------
		if ( (int)$this->m_iTitlesCounter % $this->m_iChunkSize == 0 ) {
			$this->addData('title'); 
		}
		
	}

	
	public	function	beginTitle($sRecordType)
	{
		$this->m_bInTitle = true;
		$this->m_sTitleXML = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<ROOT>\n<$sRecordType>\n";
	}

	public	function	endTitle($sRecordType)
	{
		$this->m_bInTitle = false;
		$this->m_sTitleXML .= "</$sRecordType>\n</ROOT>\n";
	}
	
	public	function	addData($sRecordType)
	{
		if ( !$this->m_bDoAddData) return; 
		$iTitlesToAdd = count ( $this->m_aRecords );
		drush_print ( "\n*** Adding next {$iTitlesToAdd} records. Running total {$this->m_iTitlesCounter} ...'$sRecordType'");
        

		switch ( $sRecordType )
		{
		case  "title" : 
			airplay_insert_update_data( $this->m_aRecords );
		break;
		case  "info_artist" : 
			airplay_insert_update_artist_info( $this->m_aRecords );
        default:
        drush_print ( "Error: unknown record type: '$sRecordType'");
        
		}
		$this->m_aRecords = array();
	}
	
	
	function findRecordType( $sFileName )
	{
		$aTypesToCheck = array( "title", "info_artist" );
		$sRecordType = "";
		$hRead = fopen( $sFileName, 'r');
		$bDone = false;
		while ( !feof($hRead) ) {
			if ( $bDone ) break;
			$sLine = fgets( $hRead );	// Read next line from the file
			
			foreach ( $aTypesToCheck as $sType ) {
				if ( strpos( $sLine, $sType ) !== false ) {
					$sRecordType = $sType;
					$bDone = true;
					break;
				}
			}
		}
		fclose( $hRead );
		return $sRecordType;
	}
	
	public	function readXMLData( $sFileName )
	{
		$sRecordType = $this->findRecordType( $sFileName );
		printf( "readXMLData: $sFileName, $sRecordType\n" );
		$this->m_bInTitle 		= false;
		$this->m_iTitlesCounter	= 0;
 		$fp = fopen( $sFileName, 'r');
 		
 		$bInTitle = false;
 		
		while ( !feof($fp) ) {
			$sLine = fgets( $fp );	// Read next line from the file
			$i = strpos( $sLine, "<$sRecordType>" );
			
			if ( strpos($sLine, "<$sRecordType>" ) !== false ) {
				$this->beginTitle($sRecordType);
				continue;
			}
			if ( strpos($sLine, "</$sRecordType>" ) !== false ) {
				$this->endTitle($sRecordType);
// 				drush_print ( $this->m_sTitleXML );
				$this->parseOneRecord( $this->m_sTitleXML, $sRecordType );
				continue;
			}
			else if ( $this->m_bInTitle ) {
				$this->m_sTitleXML .= $sLine;
			}
		}
		fclose($fp);
		drush_print ("\n*** Adding last data ***");
		$this->addData($sRecordType); 
		drush_print ( "\nTitles in total: {$this->m_iTitlesCounter}" );
	}
	
	
	public	function	&titlesGet()
	{
		return $this->m_aRecords;
	}

	
// 		<LOCALITY_CATEGORY><![CDATA[POLLUTION]]></LOCALITY_CATEGORY>
	
	// -----------------------
	// --- Debug functions ---
	// -----------------------
	public	function	dbgPrintTitles()
	{
		print "\n*** Print localities *** \n";
		foreach ( $this->m_aRecords as $aTitle ) {
			drush_print_r ($aTitle);
		}
		drush_print ( "Printed " . count ($this->m_aRecords) . "  titles" );
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
			<do_insert>record_store,artist,album,buy_album,buy_album_history</do_insert>
		</title>
		";
	}

	// ----------------------------
	// --- PRIVATE: Member data ---	
	// ----------------------------
	private		$m_sTitleXML;
	private		$m_bDoAddData;
	private		$m_bInsUpdDefault;
	private		$m_iChunkSize;
	private		$m_bInTitle;
	private		$m_iTitlesCounter;
	private		$m_aRecords				= array();	// Look up locality based on ID_VENDOR . ID_CASE 
	
}


?>







