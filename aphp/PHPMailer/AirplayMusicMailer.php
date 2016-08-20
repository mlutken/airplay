<?
	require_once ( 'class.phpmailer.php' );

	class AirplayMusicMailer extends PHPMailer
	{
		function __construct(  )
		{
			parent::__construct( );
			//$this->IsSMTP(); // telling the class to use SMTP
			$this->IsSendmail();
			$this->IsHTML(true);	// Use HTML version
		}
		
		function SetSender() {
			parent::SetFrom		( $this->From , $this->ap_sender_name);
		}
		
		/*public 		$SMTPDebug	= 1;                     // enables SMTP debug information (for testing)   1 = errors and messages   2 = messages only
		public 		$SMTPAuth    = "true";                  // enable SMTP authentication
		public 		$Host       		= "mail.airplaymusic.dk"; // sets the SMTP server
		public 		$Port       		= 587;                    // set the SMTP port for the server
		public 		$Username    = "user@airplaymusic.dk"; // SMTP account username
		public 		$Password   	= "XXXX";        // SMTP account password
		*/
		//public 		$Host       		= "localhost"; 
		public 		$ap_sender_name = "Airplay Music";
		public		$From 			= "agent@airplaymusic.dk";
		public		$CharSet		= "UTF-8";
	}
?>