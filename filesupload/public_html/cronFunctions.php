<?php
require_once ('SystemSettings.php');
require_once ('utils.php');

function getWebserviceUploadDir()
{
    return getSiteRootParent() . 'upload/files/';
}

function getWebserviceFilesStoreDir()
{
	static $sFilesStoreDir = "";
	if ( $sFilesStoreDir != "" ) return $sFilesStoreDir;
	$sFilesStoreDir = getSiteRootParent() . 'files_store/';
	if (!file_exists($sFilesStoreDir ) ) {
		mkdir( $sFilesStoreDir, 0755, true ); 
	}
	return $sFilesStoreDir;
}

function getFirstAvailableFileNumber( $sPrefix )
{
	$iNum = 0;
	$aFiles = listDir( getWebserviceUploadDir(), false, $sPrefix ) ;
	foreach ( $aFiles as $sFile ) {
		$aParts = explode ( '_', $sFile );
		if ( $aParts[1] > $iNum )	$iNum = $aParts[1];
	}
	return $iNum +1;
}

function getNextFileNumber( $sPrefix )
{
	$iNum = PHP_INT_MAX;
	$aFiles = listDir( getWebserviceUploadDir(), false, $sPrefix ) ;
	foreach ( $aFiles as $sFile ) {
		$aParts = explode ( '_', $sFile );
		if ( $aParts[1] < $iNum )	$iNum = $aParts[1];
	}
	return $iNum != PHP_INT_MAX ? $iNum : 0;
}


/** 
Get next available upload number. The 'raw' uploaded files 
are named like this: upload_#_.xml, where '#' denotes an integer 
number. This function will find the uploaded file with the highest number 
and return this number +1. */
function firstAvailableUploadNumber()
{
	return getFirstAvailableFileNumber ('upload_');
}


/** 
Get number of next file ready for parsing. The 'raw' uploaded files 
are named like this: upload_#_.xml, where '#' denotes an integer 
number. This function return the lowest number of the files available or 
0 if no files are present for parsing. */
function nextReadyForParseNumber()
{
	return getNextFileNumber ('upload_');
}


/** 
Get number of next file part currently being preocessed. The process file parts 
are named like this: processing_#_.xml, where '#' denotes an integer 
number. This function return the lowest number of the files available or 
0 if no files are present for processing. */
function nextProcessingNumber()
{
	return getNextFileNumber ('processing_');
}





/** 
Get next available upload filename. The 'raw' uploaded files 
are named like this: upload_#_.xml, where '#' denotes an integer 
number.*/
function firstAvailableUploadName( $bFullName = true )
{
	$iNum = getFirstAvailableFileNumber ('upload_');
	$sPath = $bFullName ? getWebserviceUploadDir() : '';
	return "{$sPath}upload_{$iNum}_.xml";
}


/** 
Get name of next file ready for parsing. The 'raw' uploaded files 
are named like this: upload_#_.xml, where '#' denotes an integer 
number. This function return the lowest number of the files available or 
"" (empty string) if no files are present for parsing. */
function nextReadyForParseName( $bFullName = true )
{
	$iNum = getNextFileNumber('upload_');
	$sPath = $bFullName ? getWebserviceUploadDir() : '';
	return $iNum != 0 ? "{$sPath}upload_{$iNum}_.xml" : "";
}


/** 
Get name of next file part currently being processed. The process file parts 
are named like this: processing_#_.xml, where '#' denotes an integer 
number. This function return the lowest number of the files available or 
"" (empty string) if no files are present for processing. */
function nextProcessingName( $bFullName = true )
{
	$iNum = getNextFileNumber('processing_');
	$sPath = $bFullName ? getWebserviceUploadDir() : '';
	return $iNum != 0 ? "{$sPath}processing_{$iNum}_.xml" : "";
}


/** Splits one large XML file with localities into smaller parts(files) named 
'processing_#_.xml, where '#' denotes part numer. */
function splitXmlFileForProcessing( $sFileToSplit, $iLocalitiesPerFile = 10 )
{
	$bInLocality 		= false;
	$iLocalitiesCounter	= 0;
	$iFilePartsCounter	= 1;
	$sFilePart 			= getWebserviceUploadDir() . "processing_{$iFilePartsCounter}_.xml";
	$hRead 				= fopen( $sFileToSplit, 'r');
	$bInLocality 		= false;
	
	$hWrite = fopen( $sFilePart, "w" );
	fwrite( $hWrite, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<ROOT>\n" );
	
	while ( !feof($hRead) ) {
		$sLine = fgets( $hRead );	// Read next line from the file
		
		if ( strpos($sLine, '<LOCALITY>' ) !== false ) {
			$bInLocality = true;
			fwrite( $hWrite, "<LOCALITY>\n" );
			continue;
		}
		if ( strpos($sLine, '</LOCALITY>' ) !== false ) {
 			fwrite ( $hWrite, "</LOCALITY>\n" );
			
			$bInLocality = false;
			$iLocalitiesCounter++;
			if ( $iLocalitiesCounter % $iLocalitiesPerFile == 0 ) {
				fwrite ( $hWrite, "</ROOT>\n" );
				fclose( $hWrite );
				
				$iFilePartsCounter++;
				$sFilePart = getWebserviceUploadDir() . "processing_{$iFilePartsCounter}_.xml";
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
}






?>
