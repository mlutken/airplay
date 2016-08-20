<?php 
// $Id$

class SiteMapData {
    public      $sTableName = "";
    public      $iPageIndexStart;               ///< First valid page-index for this type ( 'drupal', 'artist', 'album' or 'song')
    public      $iPageIndexLast;                ///< Last valid page-index for this type. See background info below.
    public      $iMapFileIndexStart;            ///< First valid map-file-index for this type
    public      $iMapFileIndexLast;             ///< Last valid map-file-index for this type
    public      $iItemIndexStart = 1;           ///< First valid primary_id for this item (always 1)
    public      $iItemIndexLast;                ///< Last valid primary_id for this type. 
    public      $iTotalNumberOfMapFilesAvail;   ///< The total number of map files available for this type.  

    public      $iCurLargestItemIndex;          ///< Largest item index (primary_id) in DB 
    public      $iCurLargestMapFileIndex;       ///< Current largest map file index actually written ( I.e. file actually on disk )
    public      $iCurDBLargestMapFileIndex;     ///< Current largest map file index computed from DB ( I.e. if we wrote everything from DB this is the largest numbered map index file we would get)
    public      $sSQL = "";                     ///< SQL query to get the sitemap data
    public      $aMapFileNames = array();       ///< List of all map file names for this type (absolute path on filesystem)
    public      $aMapFileUrlNames = array();    ///< List of all map URL names for this type (absolute path on server http://public.xx.xx/ )
    public      $aDrupalSourceDstMapFileNames = array();       ///< Only used for type 'drupal'
    public      $M_iPagesPerMapFile;
    
    public  function    __construct( $tableName, $rangeStart, $rangeLast, $iPagesPerMapFile ) 
    {
        $this->sTableName           = $tableName; 
        $this->iPageIndexStart      = $rangeStart;
        $this->iPageIndexLast       = $rangeLast;
        $this->M_iPagesPerMapFile   = $iPagesPerMapFile;
        $iPageIndexStart    = (int)($this->iPageIndexStart);
        $iPageIndexLast     = (int)($this->iPageIndexLast);
        $iMapFileIndexStart = (int)($iPageIndexStart+$iPagesPerMapFile -1)  / (int)($iPagesPerMapFile); 
        $iMapFileIndexLast  = $iPageIndexLast / (int)($iPagesPerMapFile); 
        $this->iMapFileIndexStart   = $iMapFileIndexStart; 
        $this->iMapFileIndexLast    = $iMapFileIndexLast;
        $this->iTotalNumberOfMapFilesAvail = (int)($rangeLast - $rangeStart +1) / (int)($iPagesPerMapFile);
        $this->iItemIndexLast       = $this->iTotalNumberOfMapFilesAvail * $iPagesPerMapFile;
    }
}


/*
Background info:
We are allowed at max 50 000 sitemap files each with 50 000 entries which yeilds 2.5 billion 
( 2.5E9 = 2 500 000 000). 
For each 'page type' we define a range in the hope that it will suffice:
General Drual pages :           1    -    10 000 000
Artist pages        :  10 000 001    -   100 000 000
Album pages         : 100 000 001    -   500 000 000
Song pages:         : 500 000 001      2 500 000 000 

*/
class SiteMapWriter 
{


	public 	function	__construct( $sCmd, $sDomain = "dk", $sBaseDir = "" )
	{
        global $conf;
        //$cwd = getCwd();
		
		// -----------------------------------------------
		// --- Initialize 'static/given' site map data ---
        // -----------------------------------------------
        $this->m_sLockFile  = "SiteMapWriter_alock";

        $this->m_aData = array (
              "drupal"  => new SiteMapData(""               ,         1,   10000000, $this->M_iPagesPerMapFile ) 
            , "artist"  => new SiteMapData("artist"         ,  10000001,  100000000, $this->M_iPagesPerMapFile ) 
            , "album"   => new SiteMapData("album_simple"   , 100000001,  500000000, $this->M_iPagesPerMapFile ) 
            , "song"    => new SiteMapData("song_simple"    , 500000001, 2500000000, $this->M_iPagesPerMapFile ) 
		);

        $this->m_aData["artist"]->sSQL  = "SELECT artist_name, artist_id FROM artist ORDER BY artist_id "; 
        $this->m_aData["album"]->sSQL   = "SELECT artist_name, album_simple_name, album_simple_id FROM album_simple INNER JOIN artist ON artist.artist_id = album_simple.artist_id ORDER BY album_simple_id "; 
        $this->m_aData["song"]->sSQL    = "SELECT artist_name, song_simple_name, song_simple_id FROM song_simple INNER JOIN artist ON artist.artist_id = song_simple.artist_id ORDER BY song_simple_id "; 

        $this->m_sCmd       = $sCmd;
        $this->m_sDomain    = $sDomain;

        $this->m_sLang      = domainToLang($sDomain);
        $this->m_sSiteUrl   = domainToSiteBaseUrl($sDomain);
    
        $language_o = $conf['language_default'];
        $language_o->language = $this->m_sLang;
        $conf['language_default'] = $language_o;

////        if ( $sBaseDir == "" )  $this->m_sBaseDir = "../public_files/sitemaps/files";
        if ( $sBaseDir == "" )  $this->m_sBaseDir = "./public_files/sitemaps/files";
        else                    $this->m_sBaseDir = $sBaseDir;  
        
        @mkdir( $this->m_sBaseDir, 0755, true ); // The @ suppresses php warnings. Here we particularly want to disregard the dir exists warning when creating
		
		printf("BaseDir: {$this->m_sBaseDir}\n");
		
		$this->m_sBaseDir = realpath($this->m_sBaseDir);
        $this->m_sDrupalSiteMapDir = realpath("./sites/default/files/xmlsitemap");
		//$aSettings = ap_read_settings();

	}



    // ----------------------------
    // --- Conversion functions --- 
    // ----------------------------

    /** Given a page index (1 - 2.5E9) the function returns the sitemap map file index 
    ( 1 - 50 000) */
    public function pageIndexToMapFileIndex ( $iPageIndex )
    {
        if ( $iPageIndex > $this->M_iPageIndexMax || $iPageIndex < 1 ) return 0;
        return (int)$iPageIndex / (int)$this->M_iPagesPerMapFile +1;
    }
    
    /** Given a page index (1 - 2.5E9) the function returns the sitemap type name string 
    ( 'drupal', 'artist', 'album', 'song') */
    public function pageIndexToTypeName ( $iPageIndex )
    {
        if ( $iPageIndex > $this->M_iPageIndexMax || $iPageIndex < 1  ) return "";
        foreach ( $this->m_aData as $type => $data ) {
            if ( $data->iPageIndexStart <= $iPageIndex && $iPageIndex <= $data->iPageIndexLast )  return $type;
        }
        return "";
    }
    
    /** Given an item name, item_id (fx: 'album', 145 ) the function returns the page index. 
    Note that the item_id is simply the primary index of the item (or item_simple) table. Currently 
    artist use it's main table and for album and songs we use album_simple, song_simple */
    public function itemIndexToPageIndex ( $sItemTypeName, $item_id )
    {
        $data = $this->m_aData[$sItemTypeName];
        $iPageIndex = 0;
        $iPageIndex = $data->iPageIndexStart + $item_id -1;
        if ( $iPageIndex < $data->iPageIndexStart || $iPageIndex > $data->iPageIndexLast ) {
            printf("ERROR: itemIndexToPageIndex ({$sItemTypeName}, {$item_id} ) out of range: {$iPageIndex}\n");
            $iPageIndex = 0;
        }
        return $iPageIndex;

    }

    /** Given an item name, item_id (fx: 'album', 145 ) the function returns 
    the corresponding sitemap map file index ( 1 - 50 000) to which the item belongs. 
    Note that the item_id is simple the primary index of the item (or item_simple) table. Currently 
    artist use it's main table and for album and songs we use album_simple, song_simple */
    public function itemIndexToMapFileIndex ( $sItemTypeName, $item_id )
    {
        $data = $this->m_aData[$sItemTypeName];
        $iMapFileIndex = 0;
        $iLocalMapFileIndex = 1;
        if ( $item_id > $this->M_iPagesPerMapFile ) {
            $iLocalMapFileIndex = (int)((int)($item_id-1) / (int)($this->M_iPagesPerMapFile)) +1;   
        }
        $iMapFileIndex = $data->iMapFileIndexStart + $iLocalMapFileIndex -1;

        if ( $iMapFileIndex < $data->iMapFileIndexStart || $iMapFileIndex > $data->iMapFileIndexLast ) {
            printf("ERROR: itemIndexToMapFileIndex ({$sItemTypeName}, {$item_id} ) out of range: {$iMapFileIndex}\n");
            $iMapFileIndex = 0;
        }
        return $iMapFileIndex;
    }

    /** Given an item name, map file index (fx: 'album', 2045 ) the function returns 
    the first item_id (item index) for the the given map file.  */
    public function mapFileIndexToItemIndexFirst ( $sItemTypeName, $iMapFileIndex )
    {
        $data = $this->m_aData[$sItemTypeName];
        $item_id = ($iMapFileIndex - $data->iMapFileIndexStart)*$this->M_iPagesPerMapFile +1;
        return $item_id;
    }


    // ----------------------------
    // --- DB related functions --- 
    // ----------------------------

    function getLastItemIndicesFromDB ()
    {
        printf("    get last item indices (item_id's) from database ... ");
        $prev_active = db_set_active( 'airplay_music' );
        foreach ( $this->m_aMusicTypeNames as $sTypeName ) {
            $sQuery = $this->m_aData[$sTypeName]->sSQL . " DESC ";
            
            $table_name = $this->m_aData[$sTypeName]->sTableName;
            $queryResult =  db_query( $sQuery, array() );
            if ( $a = db_fetch_array($queryResult)) {
                $iCurLargestItemIndex = $a["{$table_name}_id"];
                $this->m_aData[$sTypeName]->iCurLargestItemIndex = $iCurLargestItemIndex;
                $this->m_aData[$sTypeName]->iCurDBLargestMapFileIndex = $this->itemIndexToMapFileIndex($sTypeName, $iCurLargestItemIndex);
            }
        }
        db_set_active($prev_active);
        printf("Done!\n");
    }

    /** Read all the data from DB which is needed for this map file . So given 
        an item name, map file index (fx: 'album', 2045 ) we lookup what the first item_id is 
        in the table corresponding to the map file with index 2045 for album. Then we retrieve data 
        from the table starting with this item_id (item index) and 50 000 entries ahead so we have data 
        for a complete map file. */
    function getDataForOneSiteMapFile ( $sItemTypeName, $iMapFileIndex )
    {
        $prev_active = db_set_active( 'airplay_music' );
        $data = $this->m_aData[$sItemTypeName];
        
        $sQuery = $data->sSQL . " ASC ";
        $resStart_i = $this->mapFileIndexToItemIndexFirst ( $sItemTypeName, $iMapFileIndex );
        $resCount_i = $data->M_iPagesPerMapFile;
        
        printf("    getDataForOneSiteMapFile ( $sItemTypeName, $iMapFileIndex )  ItemIndex range ($resStart_i, $resCount_i)... ");
        
        $queryResult = db_query_range($sQuery, array(), $resStart_i, $resCount_i);

        $resultData_a = array();
        while ( $itemData_a = db_fetch_array($queryResult)) {
            $resultData_a[] = $itemData_a;
        }
        
        db_set_active($prev_active);
        printf("Done!\n");
        return $resultData_a;
    }

    // ------------------------------
    // --- File related functions --- 
    // ------------------------------
    /** Get full path of xml filename. */
    function getMapFileXmlName( $sItemTypeName, $iMapFileIndex )
    {
        return "{$this->m_sWorkDir}/{$sItemTypeName}_{$iMapFileIndex}_sitemap.xml";
    }

    /** Get full path of xml.gz filename. */
    function getMapFileXmlGzName( $sItemTypeName, $iMapFileIndex )
    {
        return $this->getMapFileXmlName( $sItemTypeName, $iMapFileIndex ) . ".gz";
    }

    /** Get full path of sitemapindex.xml.gz filename. */
    function getSiteMapIndexFileXmlGzName()
    {
        return $this->m_sWorkDir . "/sitemapindex.xml.gz" ;
    }

    /** Get sitemap host name. */
    function getSiteMapHostName()
    {
        $sitemapHost = "http://www.airplaymusic.dk/";
        $sitemapHost = domainToSiteBaseUrl($this->m_sDomain) ;
        ////$sitemapHost = str_replace ( "www" , "public" , $sitemapHost );
        return $sitemapHost ;
    }




    function readMapFileNamesDrupal()
    {
        printf("    read drupal file names from disk {$this->m_sDrupalSiteMapDir}  ...");
        
        $drupalFiles_a = array();
        $candidates_a = ap_list_dir( "{$this->m_sDrupalSiteMapDir}", true, "1.xml" ) ;
        foreach ( $candidates_a as $fileName ) {
            $f = fopen($fileName, "r");
            $siteUrlMatches_b = false;
            while ( !feof($f) ) {
                $line = fgets($f);
                if ( $line !== false ) {
                    if ( strpos( $line, $this->m_sSiteUrl ) !== false ) {
                        $siteUrlMatches_b = true;
                    }
                }
            }
            fclose($f);
            if ( $siteUrlMatches_b ) {
                $drupalFiles_a[] = $fileName;
            }
            
        }

        
        $this->m_aData["drupal"]->aDrupalSourceDstMapFileNames  = array();
        $this->m_aData["drupal"]->aMapFileNames                 = array();
        $this->m_aData["drupal"]->aMapFileUrlNames              = array();
        foreach ( $drupalFiles_a as $srcFileName ) {
            $dstFileName = $this->m_sWorkDir . "/" . basename($srcFileName) . ".gz";
            $this->m_aData["drupal"]->aDrupalSourceDstMapFileNames[] = array($srcFileName, $dstFileName);
            $this->m_aData["drupal"]->aMapFileNames[] = $dstFileName;

            // Create complete URL to this file
            $fp = realpath($dstFileName);
            $url = $this->getSiteMapHostName() ;
            $url .= "public_files/sitemaps/files/{$this->m_sDomain}/" . basename($fp);
            $this->m_aData["drupal"]->aMapFileUrlNames[] = $url;
        }
        printf("Done!\n");
    }


    function readMapFileNamesMusic()
    {
        printf("    read music map file names from disk ... ");
        foreach ( $this->m_aMusicTypeNames as $sTypeName ) {
            $this->m_aData[$sTypeName]->aMapFileNames =   ap_list_dir( $this->m_sWorkDir, false, $sTypeName ) ;
            $this->m_aData[$sTypeName]->aMapFileUrlNames = array();
            
            // Create complete URLs for the files
            foreach ( $this->m_aData[$sTypeName]->aMapFileNames as &$fp ) {
                $fp = realpath($fp);
                $url = $this->getSiteMapHostName() ;
                $url .= "public_files/sitemaps/files/{$this->m_sDomain}/" . basename($fp);
                $this->m_aData[$sTypeName]->aMapFileUrlNames[] = $url;
            }
            
            $iCurLargestMapFileIndex = $this->getHighestFileNumber($this->m_aData[$sTypeName]->aMapFileNames);
            if ( $iCurLargestMapFileIndex == 0) {
                $iCurLargestMapFileIndex = $this->m_aData[$sTypeName]->iMapFileIndexStart;
            }
            $this->m_aData[$sTypeName]->iCurLargestMapFileIndex   = $iCurLargestMapFileIndex;
        }
        printf("Done!\n");
    }

    function readMapFileNames()
    {
        $this->readMapFileNamesDrupal();
        $this->readMapFileNamesMusic();
    }

    /** Given an array of filenames named like 'somename_##_whatever.whatever' the highest  
    number is returned. Typical example names for this sitemap writer are: 'artist_12_sitemap.xml.gz', 
    'album_147_sitemap.xml.gz' etc. The numbers returned from the two examples would be 12 and 147 
    respectively. If no matching filename is found 0 is returned. */
    function getHighestFileNumber( $aFiles )    ///< [in] Array of filenames named like this: somename_##_whatever.whatever
    {
        $iNum = -1;
        foreach ( $aFiles as $sFile ) {
            $sBaseName = basename($sFile); 
            $aParts = explode ( '_', $sBaseName );
            if ( $aParts[1] > $iNum )   $iNum = $aParts[1];
        }
        return $iNum != -1 ? $iNum : 0;
    }

    /** Delete all files in array of filenames. */
    function deleteAllFilesInArray( $aFiles )    ///< [in] Array of filenames named like this: somename_##_whatever.whatever
    {
        foreach ( $aFiles as $sFile ) {
            if ( file_exists( $sFile) ) {
                unlink ( $sFile );
            }
        }
    }

    /** Delete all site map files. */
    function deleteAllSiteMapFiles()  
    {
        $this->readMapFileNames();
        $this->deleteAllFilesInArray( $this->m_aData["drupal"]->aMapFileNames );
        $this->deleteAllFilesInArray( $this->m_aData["artist"]->aMapFileNames );
        $this->deleteAllFilesInArray( $this->m_aData["album"]->aMapFileNames );
        $this->deleteAllFilesInArray( $this->m_aData["song"]->aMapFileNames );
        if ( file_exists( $this->getSiteMapIndexFileXmlGzName() ) ) {
            unlink( $this->getSiteMapIndexFileXmlGzName() );
        }
    }

    // -----------------------------------------
    // --- Sitemap content related functions --- 
    // -----------------------------------------


    function getSiteMapDataForOneItem( $sItemTypeName, $itemData_a )
    {
        $artist_name = $itemData_a["artist_name"];
        if ( $artist_name == "" ) return "";

        $s = "<url>";
        $url = "";
        
        switch ( $sItemTypeName ) {
            case "artist"   : 
                $url = ap_artist_url( $artist_name );
                break;
            case "album"    : 
                $album_simple_name = $itemData_a["album_simple_name"];
                if ( $album_simple_name != "" ) {
                    $url = ap_album_url( $artist_name, $album_simple_name );
                }
                break;
            case "song"     : 
                $song_simple_name = $itemData_a["song_simple_name"];
                if ( $song_simple_name != "" ) {
                    $url = ap_song_url( $artist_name, $song_simple_name );
                }
                break;
        }
        $url = ap_xml_escape($url);
        $s .= "<loc>{$this->m_sSiteUrl}{$url}</loc>";
        $s .= "</url>\n";
        if ( $url == "" )   return "";
        else                return $s;
    }

    function writeOneSiteMapFile ( $sItemTypeName, $iMapFileIndex )
    {
        $mapFileData_a  = $this->getDataForOneSiteMapFile ( $sItemTypeName, $iMapFileIndex );
        $fileName       = $this->getMapFileXmlGzName( $sItemTypeName, $iMapFileIndex );

        printf("    writeOneSiteMapFile ( $sItemTypeName, $iMapFileIndex )      File: $fileName\n");

        $f = gzopen( $fileName, "w");  
        gzwrite($f, $this->getSiteMapFileHeader() );
        foreach ( $mapFileData_a as $itemData_a ) {
            gzwrite($f, $this->getSiteMapDataForOneItem( $sItemTypeName, $itemData_a ) );
        }
        gzwrite($f, $this->getSiteMapFileFooter() );
        gzclose($f);
    }

    function getSiteMapFileHeader()
    {
        $s = "";
        $s .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $s .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
        return $s;
    }

    function getSiteMapFileFooter()
    {
        return "</urlset>\n";
    }


    function getSiteMapIndexFileHeader()
    {
        $s = "";
        $s .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $s .= "<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
        return $s;
    }

    function getSiteMapIndexFileFooter()
    {
        return "</sitemapindex>\n";
    }



    function updateSiteMapsForDrupal () 
    {
        foreach ( $this->m_aData["drupal"]->aDrupalSourceDstMapFileNames as $srcDst_a ) {
            ap_compress_gz($srcDst_a[0], $srcDst_a[1]);
        }
    }


    function updateSiteMapsForItemType ($sItemTypeName) 
    {
        $data = $this->m_aData[$sItemTypeName];
        $begin_i    = $data->iCurLargestMapFileIndex;
        $end_i      = $data->iCurDBLargestMapFileIndex;
        printf("  updateSiteMapsForItemType ($sItemTypeName) Map numbers $begin_i => $end_i ...\n");
        
        for ( $i = $begin_i; $i <= $end_i; $i++ ) {
            $this->writeOneSiteMapFile ( $sItemTypeName, $i );
        }
    }


    function updateSiteMapIndexFile () 
    {
        $this->readMapFileNames();
        $siteMapFileName = $this->getSiteMapIndexFileXmlGzName();
        $f = gzopen( $siteMapFileName, "w");  
        gzwrite($f, $this->getSiteMapIndexFileHeader() );
//        foreach ( $this->m_aAllTypeNames as $sItemTypeName ) {
        foreach ( $this->m_aMusicTypeNames as $sItemTypeName ) {
            $siteMapFileUrls_a = $this->m_aData[$sItemTypeName]->aMapFileUrlNames;
            foreach( $siteMapFileUrls_a as $url ) {
                gzwrite ( $f, "<sitemap><loc>$url</loc></sitemap>\n");
            }
        }
        gzwrite($f, $this->getSiteMapIndexFileFooter() );
        gzclose($f);
    }

    function writeHtAccessFile()
    {
        $fileName = $this->m_sWorkDir . "/.htaccess";
        printf("writeHtAccessFile: '$fileName' !\n");
        $s = "";
        $s .= "Options +Indexes\n";
        $s .= "Order allow,deny\n";
        $s .= "Allow from all\n";
        $s .= "Options +FollowSymLinks\n";
        
        $f = fopen($fileName, "w");
        fwrite( $f, $s );
        fclose($f);
    }

    function updateSiteMaps () 
    {
        printf("Updating site maps ....\n\n");
        //$this->updateSiteMapsForDrupal();
        $this->writeHtAccessFile();
        foreach ( $this->m_aMusicTypeNames as $sItemTypeName ) {
            $this->updateSiteMapsForItemType($sItemTypeName);
            printf("\n");
        }
        printf("Done! Updating site maps.\n");
        $this->updateSiteMapIndexFile();
    }
        
    function deleteSiteMaps () 
    {
        printf("Deleting all site maps ....\n");
        $this->readMapFileNames();
        $this->deleteAllSiteMapFiles();
        printf("Done! Deleting all site maps.\n");
        $this->readMapFileNames();
    }
        
    function rebuildSiteMaps () 
    {
        printf("Rebuild all sitemaps...\n");
        $this->deleteSiteMaps();
        $this->readMapFileNames();
        $this->updateSiteMaps();
    }


	function cron()
	{
		
		if ( ! $this->okToRun($this->m_sLockFile) ) {
			drush_print( "Semaphore '{$this->m_sLockFile}' locked\n" );
			return;
		}
		
		$hFile = fopen( $this->m_sLockFile, "w+");	
		writeTimeStamp($hFile);
		drush_print("Running sitemap '{$this->m_sCmd}' cron ... ");

        // ----------------------------------------
        // --- Initialize dynamic site map data ---
        // ----------------------------------------
        
        $this->m_sWorkDir = $this->m_sBaseDir . "/" . $this->m_sDomain;
        @mkdir( $this->m_sWorkDir, 0755, true ); // The @ suppresses php warnings. Here we particularly want to disregard the dir exists warning when creating

        printf("CWD:            : '%s'\n", getcwd() );
        printf("BaseDir         : '{$this->m_sBaseDir}\n" );
        printf("WorkDir         : '{$this->m_sWorkDir}\n" );
        printf("DrupalSiteMapDir: '{$this->m_sDrupalSiteMapDir}'\n" );

        $this->writeHtAccessFile();
        $this->updateSiteMapIndexFile();
        $this->readMapFileNames();
        $this->getLastItemIndicesFromDB();

		switch ( $this->m_sCmd ) {
            case "update"   : $this->updateSiteMaps(); break;
            case "delete"   : $this->deleteSiteMaps(); break;
            case "rebuild"  : $this->rebuildSiteMaps(); break;
		}
		
		unlink( $this->m_sLockFile );	// Delete the file on disk.	
        
        //$this->dbgPrint();
	}





	function okToRun ( $sLockFile )
	{
        return true; // TODO: 
		if ( $sLockFile == "" ) return true;
		
		$bOkToRun = true;
		if ( file_exists($sLockFile) ) {
			$bOkToRun = false;
			$hFile = fopen( $sLockFile, "r");
			$iTimeStampRead = readTimeStamp( $hFile );
			if ( time() - readTimeStamp( $hFile ) > 3600 )	{	// 3600 = 1 hour, 90000 = 25 hours 
				printf ("OK to run since the old instance is hanging or exited without cleaning semaphore\n");
				// Too long time expired since the (apparently) running instance
				// wrote to the timestamp file, that we assume it's hanging or has exited
				// without removing the semaphore file.
				// We delete the file and allow this instance to start.
				// NOTE: We really should find a way to actually kill, the dead process. If it is in fact still running.  !!!!
				$bOkToRun = true;
				fclose ( $hFile);
				unlink( $sLockFile );	// Delete the file on disk.	
				if ( file_exists($sLockFile) ) {
					// This should not happen unless this dir has gotten wrong owner
					logErr('Could not delete semaphore lock file', __file__);
					print( "Error: Could not delete semaphore lock file\n");
				}
			}
			else {
				fclose ( $hFile);
			}
		}
		return $bOkToRun;
	}

    function dbgPrint()
    {
        foreach ( $this->m_aAllTypeNames as $sTypeName ) {
            print_r($this->m_aData[$sTypeName]);
        }
        //print_r($this);
    }
    
    function adHocTesting()
    {
        printf("Hej hej: '{$this->M_iPagesPerMapFile}'\n");

        $this->dbgPrint();

        print_r($conf['language_default']);
        printf( "Command ('$sCmd') 1: '%s'\n", $this->m_sCmd );
        printf( "Lang ('{$this->m_sLang}')\n" );

        printf( "ap_artist_suburl: '%s'\n", ap_artist_suburl() );
        printf( "ap_album_suburl: '%s'\n", ap_album_suburl() );
        printf( "ap_song_suburl: '%s'\n", ap_song_suburl() );

        //print_r($this->m_aData);
        $i = 500000000;
        printf( "pageIndexToTypeName($i): '%s'\n", $this->pageIndexToTypeName($i) );
        $s = 'artist';
        $i = 12;
        printf( "itemIndexToPageIndex('$s', $i): '%d'\n", $this->itemIndexToPageIndex($s, $i) );
        
        printf( "Command: '%s'\n", $this->m_sCmd );
        
        $sTypeName = "album";
        $a = array(0, 1, 5, 49999, 50000, 50001, 99999, 100000, 100001, 149999, 150000, 150001
/*            ,   90000000,   90000001 
            ,  400000000,  400000001
             , 2000000000, 2000000001*/
            
            );
        foreach ( $a as $i ) {
            $iMapFileIndex = $this->itemIndexToMapFileIndex ( $sTypeName, $i );
            $iItemIndexFirst = $this->mapFileIndexToItemIndexFirst( $sTypeName, $iMapFileIndex);
            printf("itemIndexToMapFileIndex ( $sTypeName, $i ) => '$iMapFileIndex',  First: '$iItemIndexFirst'\n");
        }
    }

	// ----------------------------
	// --- PRIVATE: Member data ---	
	// ----------------------------
    private     $M_iPagesPerMapFile     = 50000;
    private     $M_iPageIndexMax        = 2500000000;
    
	private		$m_sLockFile; 
    private     $m_sCmd;
    private     $m_sDomain;
    private     $m_sSiteUrl;            ///< Current site URL e.g: 'http://www.airplaymusic.dk/', 'http://www.airplaymusic.co.uk/'
    private     $m_sLang;
    private     $m_sWorkDir;
    private     $m_sBaseDir;
    private     $m_sDrupalSiteMapDir;
    private     $m_aData = array();
    private     $m_aMapFileNames    = array( "drupal" => array(), "artist" => array(), "album" => array(), "song" => array() );
    private     $m_aMusicTypeNames  = array ("artist", "album", "song");
    private     $m_aAllTypeNames    = array ("drupal","artist", "album", "song");
	
}


/*
SELECT * FROM 
album_simple 
INNER JOIN artist ON artist.artist_id = album_simple.artist_id 
INNER JOIN album ON album.album_simple_id = album_simple.album_simple_id 
LEFT JOIN genre ON album.genre_id = genre.genre_id 

*/


