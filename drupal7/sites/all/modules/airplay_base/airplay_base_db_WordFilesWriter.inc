<?php 
// $Id$


class WordData {
    public      $word_name = "";
    
    public      $word_counts = array( "artist" => 0, "album" => 0, "song" => 0, "all" => 0  );
    public  function    __construct( $word, $itemType_s ) 
    {
        $this->word_name = $word;
        $this->word_counts = array( "artist" => 0, "album" => 0, "song" => 0, "all" => 1  );
        $this->word_counts[$itemType_s] = 1;
    }
}



/*
Background info:

*/
class WordFilesWriter 
{


	public 	function	__construct( $sCmd, $sBaseDir = "" )
	{
		
		// -----------------------------------------------
		// --- Initialize 'static/given' site map data ---
        // -----------------------------------------------
        $this->m_sLockFile  = "WordFilesWriter_alock";
        $this->m_sCmd       = $sCmd;

        if ( $sBaseDir == "" )  $this->m_sBaseDir = "./public_files/miners/files";
        else                    $this->m_sBaseDir = $sBaseDir;  
        
        @mkdir( $this->m_sBaseDir, 0755, true ); // The @ suppresses php warnings. Here we particularly want to disregard the dir exists warning when creating
		
		printf("BaseDir: {$this->m_sBaseDir}\n");
		
		$this->m_sBaseDir = realpath($this->m_sBaseDir);

	}



    // ----------------------
    // --- Util functions --- 
    // ----------------------
    function wordOk( $wordIn_s )
    {
        return mb_strlen( $wordIn_s, 'UTF-8' ) > 2;
    }



    function cleanUpWord( $wordIn_s )
    {  // `n´
        static $charsRemove_a     = array(
              "&" , "'" , '"' , ">" , "<", "?", "+", ","
            , "/", "*", "(", ")", "[", "]", "=", "#", "!"
            , "@", "%", "^", "{", "}", ",", ".", "“" 
            , "`", "´", "§", "½", "£", '$', "|", ":", "\\"
            , "%", "~", "¼", ";" );
        //static $chars_a     = array( "&"    , "'"       , "\""      , ">"   , "<"       );
        //static $replace_a   = array( "&amp;", "&apos;"  , "&quot;"  , "&gt;", "&lt;"    );
        $wordIn_s = str_replace ($charsRemove_a , array(), $wordIn_s );
        //$wordIn_s = str_replace ($chars_a, $replace_a, $wordIn_s );
        $wordIn_s = trim( $wordIn_s );
        return $wordIn_s;
    }


    // -----------------------------------------------
    // --- Word data (in memory) related functions --- 
    // -----------------------------------------------
    function addWordsToWordData ( $itemType_s, $words_a ) 
    {
        printf("addWordsToWordData( $itemType_s ) ....");
        foreach ( $words_a as $word ) {
            $wordLower = mb_strtolower( $word, 'UTF-8' );
            $wdData = &$this->m_allWordData[$wordLower];
            if ( $wdData->word_name == "" ) {
                $wdData->word_name = $word;
                $wdData->word_counts = array( "artist" => 0, "album" => 0, "song" => 0, "all" => 1  );
                $wdData->word_counts[$itemType_s] = 1;
            }
            else {
                $wdData->word_counts[$itemType_s]++;
                $wdData->word_counts["all"]++;
            }
        }
        printf("Done!\n");
    }

    // ----------------------------
    // --- DB related functions --- 
    // ----------------------------
    function addWordsToSimpleArray ( $name, &$words_a ) 
    {
        $tok = strtok($name, $this->m_delims_s );
        while ( $tok !== false ) {
            $word = $this->cleanUpWord($tok);
            if ( $this->wordOk($word) ) {
                $words_a[] = $word;
            }
            $tok = strtok( $this->m_delims_s );
        }
    }




    function readWordsFromTable ( $tableName_s, &$names_a, $start_i, $count_i ) 
    {

        printf("readWordsFromTable ( $tableName_s, $start_i, $count_i ) ....");
        $prev_active = db_set_active( 'airplay_music_v1' );
        $words_a = array();
        if ($tableName_s  == "artist") {
            $sQuery = "SELECT {$tableName_s}_name FROM {$tableName_s}";
        } else if ($tableName_s  == "album") {
            $sQuery = "SELECT item_base_name AS album_name FROM item_base WHERE item_type = 1";
        } else if ($tableName_s  == "song") {
            $sQuery = "SELECT item_base_name AS song_name FROM item_base WHERE item_type = 2";
        }
        
        $queryResult = db_query_range($sQuery, $start_i, $count_i, array() );
        
        while ( $itemData_a = $queryResult->fetchAssoc() ) {
            $name = $itemData_a["{$tableName_s}_name"];
            $this->addWordsToSimpleArray( $name, $words_a);
			$names_a[] = $name;
        }
        $itemData_a = null;
        db_set_active($prev_active);
        printf("Done!\n");
        return $words_a;
    }

    

    function insertWordsInWordTable () 
    {
        printf("insertWordsInWordTable ...");
        $prev_active = db_set_active( 'airplay_music_v1' );

        $baseQuery = "INSERT INTO word ";
        $baseQuery .= "(word_name, artist_word_count, album_word_count, song_word_count, word_count, word_length ) VALUES ";

        $i = (int)0;
        $values_a = array();
        foreach( $this->m_allWordData as $wdData ) {
            $i++;
            $strLen = mb_strlen($wdData->word_name, 'UTF-8' );
            $vals_s = "( '{$wdData->word_name}', {$wdData->word_counts['artist']}, {$wdData->word_counts['album']}, ";
            $vals_s .= "{$wdData->word_counts['song']}, {$wdData->word_counts['all']}, {$strLen} ) ";
            $values_a[] = $vals_s;
            if ( $i % 100000 == 0 ){
                if ( count($values_a) > 0 ) {
                    $query = $baseQuery . implode(',', $values_a);
                    $queryResult =  db_query($query, array() );
                    $values_a = array();
                }
            }
        }
        if ( count($values_a) > 0 ) {
            $query = $baseQuery . implode(',', $values_a);
            $queryResult =  db_query($query, array() );
        }
        //echo( "sQuery<$query>  queryResult : '$queryResult'\n");

        db_set_active($prev_active);
        printf("Done!\n");
    }


    function readAllWordsFromTable ( $itemType_s, &$names_a ) 
    {
        printf("readAllWordsFromTable ( $itemType_s ) ....\n");
        $count_i = 10000;
        $start_i = 1;
        
         
        $words_a = $this->readWordsFromTable ( $itemType_s, $names_a, $start_i, $count_i );
        while ( count($words_a) > 0 ) {
            $this->addWordsToWordData( $itemType_s, $words_a );

            //usleep ( 1000 );    // So we don't exhaust the system completely
            $start_i = $start_i + $count_i;
            $words_a = $this->readWordsFromTable ( $itemType_s, $names_a, $start_i, $count_i );
        }
        //print_r($this->m_allWordData);
    }

    function truncateWordTable () 
    {
        printf("truncateWordTable ....\n");
        $prev_active = db_set_active( 'airplay_music_v1' );
        $sQuery = "TRUNCATE TABLE word";
        $queryResult =  db_query($sQuery, array() );
        db_set_active($prev_active);
    }

    // ------------------------------
    // --- File related functions --- 
    // ------------------------------
    function writeWordsToFile ( $itemType_s ) 
    {
        printf("writeWordsToFile ( $itemType_s ) ...");
        $fileName = "{$this->m_sBaseDir}/word_{$itemType_s}.txt.gz";
        
        if ( $itemType_s == "artist_limited_chars") {
            $last_word = "";
            $f = gzopen( $fileName, "w");  
            foreach( $this->m_allWordData as $wdData ) {
                //$word = strtolower(substr($wdData->word_name, 0, 4));
                $word = mb_strtolower( substr($wdData->word_name, 0, 4), 'UTF-8' );
                /* Only append unique words */
                if ( $word != $last_word ) { 
                    gzwrite($f, "{$word}\n" );
                }
                $last_word = $word;
            }
            gzclose($f);
        } else {
            $f = gzopen( $fileName, "w");  

            foreach( $this->m_allWordData as $wdData ) {
                if ( $wdData->word_counts[$itemType_s] > 0 ) {
                    gzwrite($f, "{$wdData->word_name}\n" );
                }
            }
            gzclose($f);
        }
        printf("Done!\n");
    }

    function writeArrayToFile ( $name, $aStrings ) 
    {
        printf("writeArrayToFile ( $name ) ...");
        $fileName = "{$this->m_sBaseDir}/{$name}.txt.gz";
        $f = gzopen( $fileName, "w");  

        foreach( $aStrings as $s ) {
			$s = trim($s);
            if ( $s != "" ) {
                gzwrite($f, "{$s}\n" );
            }
        }
        gzclose($f);
        printf("Done!\n");
    }
    
    function writeHtAccessFile()
    {
        $fileName = $this->m_sBaseDir . "/.htaccess";
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


    // ----------------------
    // --- Main functions ---
    // ----------------------

    function updateArtistWords () 
    {
        printf("updateArtistWords ....\n");
        $names_a = array();
        $this->truncateWordTable();
        $this->readAllWordsFromTable("artist", $names_a);
        sort($names_a);
        $this->writeArrayToFile("names_artist", $names_a);
        $names_a = null;
		
        ksort($this->m_allWordData);
        $this->writeWordsToFile("artist");
        /* TODO: Make more flexible */
        $this->writeWordsToFile("artist_limited_chars");
        
        $this->insertWordsInWordTable();

    }
        
    function updateAlbumWords () 
    {
        printf("updateAlbumWords ....\n");
        $this->truncateWordTable();
        $this->readAllWordsFromTable("album");
        ksort($this->m_allWordData);
        
        $this->writeWordsToFile("album");
        $this->insertWordsInWordTable();
    }
        
    function updateSongWords () 
    {
        printf("updateSongWords...\n");
        $this->truncateWordTable();
        $this->readAllWordsFromTable("song");
        ksort($this->m_allWordData);

        $this->writeWordsToFile("song");
        $this->insertWordsInWordTable();
    }

    
    function updateAllWords () 
    {
        printf("updateAllWords...\n");
        $this->truncateWordTable();
        $this->readAllWordsFromTable("artist");
        $this->readAllWordsFromTable("album");
        $this->readAllWordsFromTable("song");
        ksort($this->m_allWordData);
        
        $this->writeWordsToFile("artist");
        $this->writeWordsToFile("artist_limited_chars");
        $this->writeWordsToFile("album");
        $this->writeWordsToFile("song");
        $this->writeWordsToFile("all");

        $this->insertWordsInWordTable();
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
        

        printf("CWD:            : '%s'\n", getcwd() );
        printf("BaseDir         : '{$this->m_sBaseDir}\n" );


        $this->writeHtAccessFile();
        
		switch ( $this->m_sCmd ) {
            case "truncate" : $this->truncateWordTable(); break;
            case "all"      : $this->updateAllWords(); break;
            case "artist"   : $this->updateArtistWords(); break;
            case "album"    : $this->updateAlbumWords(); break;
            case "song"     : $this->updateSongWords(); break;
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
        //print_r($this);
    }
    
    function adHocTesting()
    {
        printf("Hej hej: '{$this->M_iPagesPerMapFile}'\n");

     }

	// ----------------------------
	// --- PRIVATE: Member data ---	
	// ----------------------------
    
	private		$m_sLockFile; 
    private     $m_sCmd;
    private     $m_sDomain;
    private     $m_sBaseDir;
    private     $m_delims_s = " |-_\n\t:,.";
	private     $m_allWordData = array();
}


