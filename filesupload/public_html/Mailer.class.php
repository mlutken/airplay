<?php
require_once ('class.phpmailer.php');
require_once ('SystemSettings.php');
require_once ('logfile.class.php');

class Mailer extends PHPMailer
{
	function __construct(  )
	{
		parent::__construct( );
		$this->CharSet = "utf-8";
		$this->IsSendmail();
		//printf( "this->Sendmail {$this->Sendmail}\n" );
	}


	/**
	* Set the From and FromName properties
	* @param string $address
	* @param string $name
	* @return boolean
	*/
	public function SetFrom($address, $name = '') {
		if ( $this->Sender == "" ) $this->Sender = $address;	// To force using -f from@email.domain . Some mailservers refuse the mail if not set (sendmail).
		return parent::SetFrom($address, $name ) ;
	}

	/**
	* Evaluates the message and returns modifications for inline images and backgrounds
	* @access public
	* @return $message
	*/
	public function MsgHTML( $message, $basedir = '' ) {
        //$message = preg_replace("/[\\]/",'',$message);
		$message = eregi_replace("[\]",'',$message);
		if ( isProd() )	return parent::MsgHTML( $message, $basedir ) ;
		else {
			$this->IsHTML(true);
			$this->Body = $message;
			return true;
		}
	}

	/**
	* Evaluates the message and returns modifications for inline images and backgrounds. 
	* Also wraps the message in header,body and with correct charset set to utf-8
	* @access public
	* @return $message
	*/
	public function MsgHTMLWrapHeader( $message, $basedir = '', $addHeader = '' ) {
		$sHeader = "<html xmlns='http://www.w3.org/1999/xhtml'>\n<head>\n\t<meta http-equiv='content-type' content='text/html; charset=utf-8'/>\n$addHeader\n</head>\n<body>\n";
		$sEndDoc = "\n</body>\n</html>\n"; 

		$message = $sHeader . $message . $sEndDoc;
		return $this->MsgHTML( $message, $basedir ) ;
	}


	public function Send() {
		if ( isProd() )	{ 
			logMsg("Send: PROD\n");
			return parent::Send() ;
		}
		else {
			logMsg("Send: DEV\n");
			return $this->SendToFile();
		}
	}

	public function AddEmbeddedImage($path, $cid, $name = '', $encoding = 'base64', $type = 'application/octet-stream') 
	{
    	if ( @is_file($path) && file_exists($path) ) {
			$sLeafName = basename($path);
			$dest = getCwd()."/".$sLeafName;
			array_push($this->m_aCopiedImages, $dest );
			copy ( $path, $dest );	
			if ( !isProd() ) {
				$dest = getSiteLogDir() . $sLeafName;
			}
			copy ( $path, $dest );	
		}
		return parent::AddEmbeddedImage($path, $cid, $name, $encoding, $type ) ;
    }

	public function AddAttachmentUrl($url, $name = '', $encoding = 'base64', $type = 'application/octet-stream') 
	{
		$sDestPath = downloadFile( $url, getTempDir() );
		array_push($this->m_aDownloadedFiles, $sDestPath );
		$this->AddAttachment( $sDestPath, $name, $encoding, $type );
	}


	public function 	DeleteCopiedImages() 	
	{	
		foreach ( $this->m_aCopiedImages as $path ) {
			if ( file_exists($path) ) unlink($path); 
		}
		foreach ( $this->m_aDownloadedFiles as $path ) {
			if ( file_exists($path) ) unlink($path); 
		}
	}


	public function 	GetReplyTo() 	{	return $this->ReplyTo; }
	public function 	GetTo() 		{	return $this->to; }

	protected function SendToFile()
	{
		$mailFile = new logfile("mail.html");
		$mailFile->writeMail($this);
		return true;
	}

	protected $m_aCopiedImages = array(); 
	protected $m_aDownloadedFiles = array(); 

}


?>


