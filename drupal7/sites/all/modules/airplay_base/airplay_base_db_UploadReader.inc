<?php


class UploadReader 
{

	public 	function	__construct( $sUploadDir )
	{
		$this->m_sUploadDir			= $sUploadDir . "/";
		$this->m_iTitlesPerPart		= 100;
		$this->m_sLockFile			= $this->m_sUploadDir . "_alock";
// 		$this->m_sLockFile			= "";
		$this->m_bInsUpdDefault		= false;
		$this->m_iFilesToReadPerCronInvocation = 1;
		$this->m_sProcessingName	= "processing";
	}

	function titlesPerPartSet		( $iTitlesPerPart )		{ $this->m_iTitlesPerPart = $iTitlesPerPart; 	}
	function insertUpdateDefaultSet	( $bInsUpdDefault )		{ $this->m_bInsUpdDefault = $bInsUpdDefault;	}
	function filesToReadPerCronInvocationSet	( $iFilesToReadPerCronInvocation) { $this->m_iFilesToReadPerCronInvocation = $iFilesToReadPerCronInvocation;	}
	function processingXmlNameSet	( $sProcessingName )		{ $this->m_sProcessingName = $sProcessingName;	}
	function lockFileSet				( $sLockFile )			{ $this->m_sLockFile = $this->m_sUploadDir . $sLockFile; 				}
// // 	function uploadDirGet		( )						{ return $this->m_sUploadDir; 					}

	function cron()
	{
	
		if ( ! $this->okToRun() ) {
			drush_print( "Semaphore '{$this->m_sLockFile}' locked\n" );
			return;
		}
		
		$hFile = fopen( $this->m_sLockFile, "w+");	
		writeTimeStamp($hFile);
// // 		logMsg( "Running ...", __file__ ); printf( "Running ...\n" );
		drush_print("Running cron ... ");
		for ( $n = 0; $n < $this->m_iFilesToReadPerCronInvocation; $n++ ) {
			$nxtXMLFile = $this->getNextXMLFile();
			if ( file_exists( $nxtXMLFile) ) {
				drush_print("Reading file: $nxtXMLFile");
				$xmlRead = new AirplayXMLReader( true, $this->m_bInsUpdDefault );
				$xmlRead->readXMLData( $nxtXMLFile );
				unlink ( $nxtXMLFile );
			}
			else {
				drush_print("No files in upload directory: '{$this->m_sUploadDir}'");
			}
		}
		unlink( $this->m_sLockFile );	// Delete the file on disk.	

	}

	function getNextXMLFile()
	{
		
		$sNextProcessingPart = $this->nextProcessingName(true)	;
		if ( $sNextProcessingPart != "" )	return $sNextProcessingPart;
		
		$sNextForSplitFile = $this->nextReadyForSplitName(true);
		if ( $sNextForSplitFile == "" )	return "";				// No more files to parse at this moment
		
		// Split the next file into process parts, delete the 
		// original uploaded file and return first part of the splitted file.
		$this->splitXmlFileForProcessing ( $sNextForSplitFile );	
		unlink ( $sNextForSplitFile );			
		return $this->nextProcessingName(true)	;
	}

	
	function getNextFileNumber( $sPrefix )
	{
		$iNum = PHP_INT_MAX;
		$aFiles = ap_list_dir( $this->m_sUploadDir, false, $sPrefix ) ;
		foreach ( $aFiles as $sFile ) {
			$aParts = explode ( '_', $sFile );
			if ( $aParts[1] < $iNum )	$iNum = $aParts[1];
		}
		return $iNum != PHP_INT_MAX ? $iNum : 0;
	}
	
	
	/** 
	Get name of next file ready for splitting. The 'raw' uploaded files 
	are named like this: upload_#_.xml, where '#' denotes an integer 
	number. This function return the lowest number of the files available or 
	"" (empty string) if no files are present for splitting. */
	function nextReadyForSplitName( $bFullName = true )
	{
		$iNum = $this->getNextFileNumber('upload_');
		$sPath = $bFullName ? $this->m_sUploadDir : '';
		return $iNum != 0 ? "{$sPath}upload_{$iNum}_.xml" : "";
	}
	
	
	/** 
	Get name of next file part currently being processed. The process file parts 
	are named like this: processing_#_.xml, where '#' denotes an integer 
	number. This function return the lowest number of the files available or 
	"" (empty string) if no files are present for processing. */
	function nextProcessingName( $bFullName = true )
	{
		$iNum = $this->getNextFileNumber("{$this->m_sProcessingName}_");
		$sPath = $bFullName ? $this->m_sUploadDir : '';
		return $iNum != 0 ? "{$sPath}{$this->m_sProcessingName}_{$iNum}_.xml" : "";
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
	


	/** Splits one large XML file with localities into smaller parts(files) named 
	'processing_#_.xml, where '#' denotes part number. */
	function splitXmlFileForProcessing( $sFileToSplit )
	{
		$sRecordType = $this->findRecordType( $sFileToSplit );

		$bInLocality 		= false;
		$iLocalitiesCounter	= 0;
		$iFilePartsCounter	= 1;
		$sFilePart 			= $this->m_sUploadDir . "{$this->m_sProcessingName}_{$iFilePartsCounter}_.xml";
		$hRead 				= fopen( $sFileToSplit, 'r');
		$bInLocality 		= false;
		
		drush_print("splitXmlFileForProcessing : $sFileToSplit,  $sRecordType");

		
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
					$sFilePart = $this->m_sUploadDir . "{$this->m_sProcessingName}_{$iFilePartsCounter}_.xml";
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



	function okToRun ()
	{
		if ( $this->m_sLockFile == "" ) return true;
		
		$bOkToRun = true;
		if ( file_exists($this->m_sLockFile) ) {
			$bOkToRun = false;
			$hFile = fopen( $this->m_sLockFile, "r");
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
				unlink( $this->m_sLockFile );	// Delete the file on disk.	
				if ( file_exists($this->m_sLockFile) ) {
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


	// -----------------------
	// --- Debug functions ---
	// -----------------------
	public	function	dbgPrintTitles()
	{
	}


	// ----------------------------
	// --- PRIVATE: Member data ---	
	// ----------------------------
	private		$m_sUploadDir;
	private		$m_iTitlesPerPart;
	private		$m_sLockFile;
	private		$m_bInsUpdDefault;
	private		$m_iFilesToReadPerCronInvocation;
}



?>







