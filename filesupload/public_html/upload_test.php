<?php
set_time_limit(60000);
require_once ("utils.php");
require_once ('cronFunctions.php');
require_once ('logfile.class.php');
require_once ("Mailer.class.php");

ini_set('post_max_size', '2048M');
ini_set('upload_max_filesize', '2048M');




try {
    $dir = getWebserviceUploadDir();
    if (!is_dir($dir)) {
        mkdir( $dir, 0777, true ); 
    }
    $dir = getWebserviceFilesStoreDir();
    if (!is_dir($dir)) {
        mkdir( $dir, 0777, true ); 
    }
}
catch (exception $e) {
	logErr("Unexpected exception", __file__);
	return '<root><error_code>4</error_code><error_description>' . $e->getMessage() . '</error_description></root>';
}

$sFileName = basename( $_FILES['uploadedfile']['name'] );
$sTmpUploadName =$_FILES['uploadedfile']['tmp_name'];
$iFileSize = number_format($_FILES['uploadedfile']['size']);
$sUploadPath = getWebserviceUploadDir() . $sFileName;

$pathParts = pathinfo($sFileName);
$sExtension = $pathParts['extension'];


// echo "sFileName=" .        $sFileName . "<br />"; 
// echo "Source=" .        $_FILES['uploadedfile']['name'] . "<br />"; 
// echo "destinationDir=" .  $destinationDir . "<br />"; 
// echo "sUploadPath=" . $sUploadPath . "<br />"; 
// echo "sExtension=" . $sExtension . "<br />"; 
// echo "Size=" .          $_FILES['uploadedfile']['size'] . "<br />"; 
// echo "sTmpUploadName=" . $sTmpUploadName . "<br />"; 
// echo "getWebserviceFilesStoreDir=" . getWebserviceFilesStoreDir() . "<br />"; 
// echo " copy ( $sTmpUploadName, $sUploadPath ) <br>";
// print_r($_FILES);

$bOk = true;
$bIllegalFileExt = true;
////if ( true ) {
if ( true ) {
	$bIllegalFileExt = false;
	// Only accept files with xml or gz extension to make it harder for hackers
	// Also we return success with all other filetypes even though we never accept them
	//if ( copy ( $sTmpUploadName, $sUploadPath ) ) {
	if ( move_uploaded_file( $sTmpUploadName, $sUploadPath)) {
		copy ( $sUploadPath, getWebserviceFilesStoreDir() . $sFileName );
	} else{
		$bOk = false;
	}
}

if ( $bOk ) {
	logMsg ( "Uploaded: '$sFileName' ($iFileSize bytes)" );
	echo "<b>The file '$sFileName' ($iFileSize bytes) has been uploaded</b><br/>";
} else{
	logErr ("There was an error uploading the file '$sFileName'");
	echo "There was an error uploading the file '$sFileName', please try again!<br>";
}
echo "<b>getWebserviceFilesStoreDir: " . getWebserviceFilesStoreDir() . "</b><br/>";
echo "<b>getWebserviceFilesStoreDir: " . getWebserviceFilesStoreDir() . "</b><br/>";

//sendUploadMailToAdmin( $sFileName, $bOk, $bIllegalFileExt );

// curl -F uploadedfile=@22M.avi http://jordenergiftig.dk.localhost/webservice/upload.php


function	sendUploadMailToAdmin( $sFileName, $bUploadedOk, $bIllegalFileExt )
{
	global $iFileSize;
	
	$sIllegalFileExt = "";
	if ( $bIllegalFileExt )	$sIllegalFileExt = "<b>POSSIBLE HACKER ATTACK: File did not have a legal extension<br>\n";
	
	
	$sIPClient = getRealIpAddr();
	//logMsg ("isProd(): '" . isProd() . "'" );
	$mail             = new Mailer(); 
	$bodySuccess       = "   
	<div style='width:640px;font-family: Arial, Helvetica, sans-serif; font-size: 15px;'>\n
		<h3>" . "File uploaded sucessfully!" . "</h3>\n" .
		"<b>File: '" . $sFileName . "'<br>\n" .		
		"<b>Size: '" . $iFileSize . "'<br>\n" .		
		"<b>IP client:'" . $sIPClient . "'<br>\n" .		
		"$sIllegalFileExt" . 
	"</div>\n";
	$bodyError       = "   
	<div style='width:640px;font-family: Arial, Helvetica, sans-serif; font-size: 15px;'>\n
		<h3>" . "Error uploading file!" . "</h3>\n" .
		"<b>File: '" . $sFileName . "'<br>\n" .		
		"<b>Size: '" . $iFileSize . "'<br>\n" .		
		"<b>IP client:'" . $sIPClient . "'<br>\n" .		
	"</div>\n";
	
	$mail->Sender = "upload@airplaymusic.dk"; 
	$mail->SetFrom( "upload@airplaymusic.dk", "Upload Airplay Music" );
	
	$body = $bodySuccess;
	if ( ! $bUploadedOk ) $body = $bodyError;
	
	$mail->MsgHTMLWrapHeader($body);
	$mail->AddAddress("nitram@lutken.dk", "Admin AirplayMusic" );
    $mail->AddAddress("martin.udvikling@gmail.com", "Martin Udvikling");
    $mail->AddAddress("jc@airplaymusic.dk", "Jacob Christiansen");

	
	$mail->Subject    = "File '$sFileName' uploaded sucessfully to: airplaymusic.dk" ;
	if ( ! $bUploadedOk )  $mail->Subject    = "Error uploading file to: airplaymusic.dk" ;
	$mail->AltBody    = "For at se denne Email, skal du have slået HTML visning til i dit e-mail program!"; 
	if (!$mail->Send() ) logErr ( "Mailer Error: " . $mail->ErrorInfo, __file__ );
	$mail->DeleteCopiedImages();

}


?>

<p> <a href="dataUpload_test.php" >Back to upload form TEST</a> </p>

