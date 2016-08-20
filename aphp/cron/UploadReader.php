<?php
require_once ( __DIR__ . '/../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');
require_once ('db_inserter/XmlDataReader.php');
require_once ('db_api/db_helpers.php');

class UploadReader 
{

	public 	function	__construct( $sNormalUploadDir, $sPriorityUploadDir )
	{
		$this->m_sNormalUploadDir	= $sNormalUploadDir . "/";
		$this->m_sPriorityUploadDir	= $sPriorityUploadDir . "/";
		$this->m_iTitlesPerPart		= 500;
		$this->m_sLockFile			= '/tmp/_lock_Airplay_UploadReader';
		date_default_timezone_set('Europe/Copenhagen');
	}

	function cron()
	{
		/* Test if it is ok to run script */
		if ( ! $this->okToRun() ) {
			printf( "\nSemaphore '{$this->m_sLockFile}' locked\n" );
			return;
		}
		// Lock for only now
		$hFile = fopen( $this->m_sLockFile, "w+");	
		$this->writeTimeStamp($hFile);
		
		printf("\nRunning cron ... \n");
		$this->m_tsEndTime = time() + 60*9; // Run for 9 minutes + the time needed to handle the last XML file
//		$this->m_tsEndTime = time() + 60*1; // TODO: Debug only

		while ( time() < $this->m_tsEndTime ) {
			$this->chooseCurrentUploadDir();
			$nxtXMLFile = $this->getNextXMLFile();
			
			if ( file_exists( $nxtXMLFile) ) {
				printf("\nReading file: $nxtXMLFile\n");
				$xmlRead = new XmlDataReader();
				$xmlRead->readXMLData( $nxtXMLFile );
				unlink ( $nxtXMLFile );
			}
			else {
				sleep(1);
			}
		}
		printf("\nTime up or no more files\n");
		unlink( $this->m_sLockFile );	// Delete the file on disk.	
	}

	/** Sets current upload dir to be either the normal or the priority one. If the priority upload dir 
		has any xml or gz files it will be chosen as current upload dir, otherwise the normal one is 
		chosen. */
	function chooseCurrentUploadDir()
	{
		$this->m_sCurUploadDir = $this->m_sNormalUploadDir;
		if ( file_exists($this->m_sPriorityUploadDir) ) {
			$aFiles = glob( $this->m_sPriorityUploadDir . "{*.xml,*.gz}", GLOB_BRACE);
			if ( count($aFiles) > 0 ) $this->m_sCurUploadDir = $this->m_sPriorityUploadDir;
		}
		printf("chooseCurrentUploadDir: '{$this->m_sCurUploadDir}'\n");
	}
	
	
	function getNextXMLFile()
	{
		$sNextProcessingPart = $this->nextProcessingName();
		printf("\nNext Processing Part: " . $sNextProcessingPart);
		
		if ( $sNextProcessingPart != "" ) {
			return $sNextProcessingPart;
		} else {
			printf("** no file **");
		}
		
		$sNextForSplitFile = $this->nextReadyForSplitName();
		printf("\nNext for split file: " . $sNextForSplitFile);
		
		if ( $sNextForSplitFile == "" )	{
			printf("** no file **\n");
			return "";				// No more files to parse at this moment
		}
		// Split the next file into process parts, delete the 
		// original uploaded file and return first part of the splitted file.
		$this->splitXmlFileForProcessing ( $sNextForSplitFile );	
		unlink ( $sNextForSplitFile );		// Delete the file on disk.
		return $this->nextProcessingName()	;
	}

	/** 
	Get name of next file ready for splitting. Files which are splitted are name filename_processing_.xml 
	/ ending width underscore '_processing_' . */
	function nextReadyForSplitName()
	{
		$filename = "";

		$aFiles = glob( $this->m_sCurUploadDir . "{*.xml,*.gz}", GLOB_BRACE);
		if ( count($aFiles) == 0 ) return '';

        usort($aFiles, "getOldestFile");
		
		$filename = $aFiles[0];
		if ( file_exists($filename) ) {
			$path_info = pathinfo($filename);
			$file_extension = $path_info['extension'];
			
			printf("file_extension: '$file_extension'\n");
			
			// If last file is a compressed then uncompress and run function again.
			if ($file_extension == "gz") {
				$dstFilePath = str_replace ( '.gz' , '' , $filename );
				printf("\nUncompressed file: " . $dstFilePath);
				$this->uncompress($filename, $dstFilePath );
				return '';
			}
			return $filename;
		}
		return '';
	}
	
	
	/** 
	Get name of next file part currently being processed. The process file parts 
	are named like this: #_processing_.xml, where '#' denotes an integer 
	number. This function return the lowest number of the files available or 
	"" (empty string) if no files are present for processing. */

	function nextProcessingName()
	{
		$filename = "";
		$filename_without_extension = "";
		
		$aFiles = glob( $this->m_sCurUploadDir . "{*_processing_.xml}", GLOB_BRACE);

		if ( count($aFiles) == 0 ) return '';
		
		usort($aFiles, "getOldestFile");

		$filename = $aFiles[0];
		return $filename;
	}
	
	function findRecordType( $sFileName )
	{
		$aTypesToCheck = array( "title", "info_artist" );
		$sRecordType = "";
		if ( file_exists( $sFileName) ) {
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
		}
		return $sRecordType;
	}
	


	/** Splits one large XML file with localities into smaller parts(files) named 
	'processing_#_.xml, where '#' denotes part number. */
	function splitXmlFileForProcessing( $sFileToSplit )
	{
		$sRecordType 		= $this->findRecordType( $sFileToSplit );

		$path_parts = pathinfo($sFileToSplit);

		$base_file_name = $path_parts['filename'];
		
		$bInLocality 		= false;
		$iLocalitiesCounter	= 0;
		$iFilePartsCounter	= 1;
		$sFilePart 			= $this->m_sCurUploadDir . "{$base_file_name}_{$iFilePartsCounter}_processing_.xml";
		$hRead 				= fopen( $sFileToSplit, 'r');
		$bInLocality 		= false;
		
		printf("\nsplitXmlFileForProcessing : $sFileToSplit,  $sRecordType\n");
		
        if ( file_exists($sFileToSplit) ) {
            $hWrite = fopen( $sFilePart, "w" );
            fwrite( $hWrite, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<ROOT>\n" );
            
            while ( !feof($hRead) ) {
                $sLine = fgets( $hRead );	// Read next line from the file
                
                if ( strpos($sLine, "<$sRecordType>" ) !== false ) {
                    $bInLocality = true;
                    fwrite( $hWrite, "<$sRecordType>\n" );
                    continue;
                }
                if ( strpos($sLine, "</$sRecordType>" ) !== false ) {
                    fwrite ( $hWrite, "</$sRecordType>\n" );
                    
                    $bInLocality = false;
                    $iLocalitiesCounter++;
                    if ( $iLocalitiesCounter % $this->m_iTitlesPerPart == 0 ) {
                        fwrite ( $hWrite, "</ROOT>\n" );
                        fclose( $hWrite );
                        $iFilePartsCounter++;
                        $sFilePart = $this->m_sCurUploadDir . "{$base_file_name}_{$iFilePartsCounter}_processing_.xml";
                        $hWrite = fopen( $sFilePart, "w" );
                        fwrite( $hWrite, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<ROOT>\n" );
                    }
                    continue;
                }
                else if ( $bInLocality ) {
                    fwrite( $hWrite, $sLine );
                }
            }
            fwrite ( $hWrite, "</ROOT>\n" );
            fclose( $hWrite );
            fclose( $hRead );
          }
	}

	/** Function to test if it is ok to run - is a _alock file in the directory	*/
	function okToRun ()
	{
		if ( $this->m_sLockFile == "" ) return true;
		
		$bOkToRun = true;
		if ( file_exists($this->m_sLockFile) ) {
			$bOkToRun = false;
            
            $iTimeStampRead = $this->readTimeStampFromLockFile();
            $diff = time() - $iTimeStampRead;
			if ( time() - $iTimeStampRead > 3600 )	{	// 300 = 10 min, 3600 = 1 hour, 90000 = 25 hours 
				printf ("OK to run since the old instance is hanging or exited without cleaning semaphore\n");
				// Too long time expired since the (apparently) running instance
				// wrote to the timestamp file, that we assume it's hanging or has exited
				// without removing the semaphore file.
				// We delete the file and allow this instance to start.
				// We are actually killing the process, if it is in fact still running.
                $process_id = $this->readProcessIDFromLockFile();
                /* KILL old instance */
                if ($process_id !=  0) {
					printf("KILL: $process_id\n");
                    $this->processKill($process_id);
                    $this->processForceKill($process_id);
                    $this->writeProcessKilled($process_id);
                }
				$bOkToRun = true;
				unlink( $this->m_sLockFile );	// Delete the file on disk.	
				if ( file_exists($this->m_sLockFile) ) {
					// This should not happen unless this dir has gotten wrong owner
					printf("Could not delete semaphore lock file", __file__);
					printf("Error: Could not delete semaphore lock file\n");
				}
			}
		}
		return $bOkToRun;
	}


	/** Read a timestamp from a file. Assumes file are open and readable. 
	\return The unix-style time as an integer.
	\sa http://dk.php.net/manual/en/function.strtotime.php */
	function readTimeStamp( $hFile ) ///< Handle to open readable file
	{
		rewind($hFile);						// Read from beginning
		$sFormattedRead = fread( $hFile, 30 );
		$iTimeStampRead = strtotime($sFormattedRead);
		return $iTimeStampRead;
	}
    /*
        Read timestamp from file
    */
    function readTimeStampFromLockFile() {
        $aLines = file( $this->m_sLockFile);
        $iTimeStampRead = strtotime($aLines[0]);
        return $iTimeStampRead;
    }
    /*
        Read process ID from file
    */
    function readProcessIDFromLockFile() {
        $aLines = file( $this->m_sLockFile);
        return (int)$aLines[1];
    }
    
    /** Force kill a process. */
    function processKill ( $process_id )
    {
        shell_exec("/bin/kill ${process_id} >/dev/null 2>&1");
    }

    /** Force kill a process. */
    function processForceKill ( $process_id )
    {
        shell_exec("/bin/kill -9 ${process_id} >/dev/null 2>&1");
    }
    
	/** Write a timestamp from a file. Assumes file are open and readable. 
	The timestamp is written in ISO 8601 format.
	\return The timestamp actually written ( the a unix-style time as an integer).
	\sa http://dk.php.net/manual/en/function.date.php
	\sa http://dk.php.net/manual/en/function.time.php */
	function writeTimeStamp( $hFile ) ///< Handle to open writable file
	{
		rewind($hFile);								// Write from beginning
		$iTimeStampWrite = time();					// Current time measured in the number of seconds since the Unix Epoch (January 1 1970 00:00:00 GMT). 
		$sDateWrite = date("c", $iTimeStampWrite );	// "c" formats as ISO 8601 date, see http://dk.php.net/manual/en/function.date.php
		fwrite( $hFile, $sDateWrite . "\n" . getmypid() );
		return $iTimeStampWrite;
	}

	/// uncompress("test.gz","test.php");
	function uncompress($srcName, $dstName) {
		$iBlockSize = 8192;
		$fp = fopen($dstName, "w");
		$zp = gzopen($srcName, "r");
		$contents = '';
		while (!gzeof($zp)) {
			$contents = gzread($zp, $iBlockSize);
			fwrite  ( $fp  , $contents );		
		}
		fclose($fp);
		gzclose($zp);
		unlink($srcName);
	} 

	function writeProcessKilled( $process_id )
	{
        $iTimeStampWrite = time();					// Current time measured in the number of seconds since the Unix Epoch (January 1 1970 00:00:00 GMT). 
		$sDateWrite = date("c", $iTimeStampWrite );	// "c" formats as ISO 8601 date, see http://dk.php.net/manual/en/function.date.php
		$file = fopen("/tmp/upload_reader_process_killed.log", "a+");
		fwrite($file, "Process ID " . $process_id . " killed at " . $sDateWrite . " \n" );
        fclose($file);
	}
	// ----------------------------
	// --- PRIVATE: Member data ---	
	// ----------------------------
	private		$m_sCurUploadDir;
	private		$m_sNormalUploadDir;
	private		$m_sPriorityUploadDir;
	
	private		$m_iTitlesPerPart;
	private		$m_sLockFile;
	private		$m_tsEndTime;
}

?>
