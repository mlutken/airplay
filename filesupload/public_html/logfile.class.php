<?php

require_once ('SystemSettings.php');
require_once ('class.phpmailer.php');
require_once ('Mailer.class.php');

class logfile
{
    static private $m_pInstance = null; ///< Instance pointer.

    /** Instance function. */
    static public function I()
    {
        if (logfile::$m_pInstance == null) {
            logfile::$m_pInstance = new logfile();
        }
        return logfile::$m_pInstance;
    }
    // ------------
    var $logfile;
    var $newLine = "\r\n";
    var $filesIsWriteAble = false;
    var $m_bIsMailLog = false;

    function __construct( $sLogName = 'logfile.log' )
    {
    	$sDir = getSiteLogDir();
		if ( !file_exists ($sDir) ) mkdir( $sDir, 0755, true ); // The @ suppresses php warnings. Here we particularly want to disregard the dir exists warning when creating
		if ( $sLogName == "mail.html" )	{
			$this->m_bIsMailLog = true;
		}


        $this->logfile = getSiteLogDir() . date("d") . date("m") . '_' . $sLogName;

        if (!is_writable($this->logfile)) {
			//printf("<br>NEW LOG FILE<br>\n");
            $fp = fopen($this->logfile, 'w+');
            ftruncate($fp, 0);
			if ( $this->m_bIsMailLog ) {
				fwrite($fp, "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>\n" ); 
				fwrite($fp, "<html xmlns='http://www.w3.org/1999/xhtml'>\n" ); 
				fwrite($fp, "<head>\n" ); 
				fwrite($fp, "<meta http-equiv='content-type' content='text/html; charset=utf-8'/>\n" ); 
				fwrite($fp, "</head>\n<body>\n" ); 
			}
            fclose($fp);
        }
    }



    function truncFile($file)
    {
        $fp = fopen($file, 'w+');
        ftruncate($fp, 0);
        $this->writeLog("Log Truncated", __file__);
        fclose($fp);
    }

    function truncateLog()
    {
        $this->truncFile($this->logfile);
    }

    function writeMail( $mail )
    {
    	$sStyleSUBJECT = "font-size:15pt";
    	$sStyleTABLE = "border: 3px solid #000";
    	$sStyleTH = "font-weight:bold;border: 1px solid #000";
    	$sStyleTD = "border: 1px solid #000";
		$fh = fopen( $this->logfile, 'a');
		
		fwrite($fh, "\n\n<br>\n<table style='$sStyleTABLE' >\n" );
		fwrite($fh, "\t<tr>\n" );
		fwrite($fh, "\t\t<td style='$sStyleTH' >Subject:</td>\n" );
		fwrite($fh, "\t\t<td style='$sStyleTD;$sStyleSUBJECT' >{$mail->Subject}</td>\n" );
		fwrite($fh, "\t</tr><tr>\n" );
		fwrite($fh, "\t\t<td style='$sStyleTH' >To:</td>\n" );
		fwrite($fh, "\t\t<td style='$sStyleTD' >");
		foreach ( $mail->GetTo() as $to ) fwrite($fh, "{$to[1]}&nbsp;<i>({$to[0]})</i>,&nbsp;" );
		fwrite($fh, "</td>\n");
		fwrite($fh, "\t</tr><tr>\n" );
		fwrite($fh, "\t\t<td style='$sStyleTH' >Reply To:</td>\n" );
		fwrite($fh, "\t\t<td style='$sStyleTD' >");
		foreach ( $mail->GetReplyTo() as $to ) fwrite($fh, "{$to[1]}&nbsp;<i>({$to[0]})</i>,&nbsp;" );
		fwrite($fh, "</td>\n");
		fwrite($fh, "\t</tr><tr>\n" );
		fwrite($fh, "\t\t<td style='$sStyleTH' >From:</td>\n" );
		fwrite($fh, "\t\t<td style='$sStyleTD' >{$mail->FromName}&nbsp;<i>({$mail->From})</i>" );
		fwrite($fh, "&nbsp;&nbsp;<b>Sender(envelope):</b>&nbsp;<i>{$mail->Sender}</i></td>\n" );
		fwrite($fh, "\t</tr><tr>\n" );
		fwrite($fh, "\t\t<td style='$sStyleTH' >Attachments:</td>\n" );
		fwrite($fh, "\t\t<td style='$sStyleTD' >");
		foreach ( $mail->GetAttachments() as $attach ) fwrite($fh, "{$attach[1]}&nbsp;<i>({$attach[0]})</i>,&nbsp;" );
		fwrite($fh, "</td>\n");
		fwrite($fh, "\t</tr><tr>\n" );
		fwrite($fh, "\t\t<td colspan=2 style='$sStyleTD' >{$mail->Body}</td>\n" );
		fwrite($fh, "\t</tr>\n" );

		fwrite($fh, "</table>\n" );
		fclose($fh);
    }

    function writeError($object, $filename = '')
    {
        $this->writeFile($object, "ERROR   ", $filename);
    }

    function writeLog($object, $filename = '')
    {
        $this->writeFile($object, "INFO    ", $filename);
    }

    function writeSQL($object)
    {
        $this->writeFile($object, "SQL");
    }

    function writeFile($object, $type = "DEFAULT ", $filename = '')
    {
        switch ($type) {
            case "SQL":

                $writeToFile = $this->logfile;
                $logEntry = $object . $this->newLine;

                break;
            default:
                if (is_array($object)) {

                    $logEntry = $this->newLine . $this->newLine . date('H:i:s');
                    $logEntry .= " | " . $type . ": PRINTOUT Array ";

                    foreach ($object as $key => $value) {
                        $logEntry .= $this->newLine . "                             : |  $key => $value ";
                    }

                    $logEntry .= $this->newLine . $this->newLine;

                } else {

                    $logEntry = $this->newLine . date('H:i:s') . " | " . $type . ": | <" . $filename .
                        '> ' . trim($object);
                }
                $writeToFile = $this->logfile;
                break;
        }

        // Let's make sure the file exists and is writable first.
        if ($this->filesIsWriteAble || is_writable($writeToFile)) {

            // In our example we're opening $this->logfile in append mode.
            // The file pointer is at the bottom of the file hence
            // that's where $somecontent will go when we fwrite() it.
            if (!$handle = fopen($writeToFile, 'a')) {
                echo "Cannot open file ($writeToFile)";
                return false;
            }

            // Write $somecontent to our opened file.
            if (fwrite($handle, $logEntry) === false) {
                echo "Cannot write to file ($writeToFile)";
                return false;

            }
            $filesIsWriteAble = true;
            fclose($handle);

        } else {
            echo "The file $writeToFile is not writable<br>\n";
            return false;
        }

        return true;
    }

    function unittest()
    {
        $test1 = $this->writeFile("UNITTEST!!");
        print ("<div>LOGFILE: ");
        if ($test1) {
            print ("PASS</div>\n");
            return true;
        } else {
            print ("FAIL - writeTest</div> \n ");
            return false;
        }

    }
}


/** Global error write function for simple use. */
function logErr( $object, $filename = '' )
{
	return logfile::I()->writeError($object, $filename );
}

/** Global msg/info write function for simple use. */
function logMsg( $object, $filename = '' )
{
	return logfile::I()->writeLog($object, $filename );
}


?>