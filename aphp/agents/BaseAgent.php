<?php
require_once ("agents/AgentFactory.php");

class BaseAgent
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( )
    {
		global $g_MySqlPDO;
		$this->m_dbPDO = $g_MySqlPDO;
    }

	/**
	* Get HTML for sending validate mail
	*/
	public function getValidateMailHTML($display_name, $url) {
		$sHtml = "";
		$sHtml .= $this->getMailHTMLTop();
		
		// Main content
		$sHtml .= "<table cellpadding='0' cellspacing='10' bgcolor='ffffff' width='600' align='center'>";
		$sHtml .= "<tr><td>";
		// Inner content top text
		$sHtml .= "<table border='0' cellpadding='10' cellspacing='0' width='500' align='center' style='font-family:Helvetica,sans-serif,Arial;'>";
		$sHtml .= "<tr><td style='font-size:20px;font-weight:bold;'>Kære {$display_name}</td></tr>";
		$sHtml .= "<tr><td style='font-size:14px;'>Du modtager denne mail fordi du har oprettet Agent hos Airplay Music</td></tr>";
		$sHtml .= "<tr><td style='font-size:14px;'>Her følger aktiveringslink til validering af din mail-adresse.</td></tr>";
		$sHtml .= "</table>";
		// Inner content links and stuff
		$sHtml .= "<table border='0' cellpadding='10' cellspacing='0' width='500' align='center' style='font-family:Helvetica,sans-serif,Arial;'>";
		$sHtml .= "<tr><td style='font-size:20px;font-weight:bold;' align='center'><a href='{$url}' target='_blank' style='color:#E55D09;'>Valider din mail-adresse</a></td></tr>";
		$sHtml .= "</table>";
		
		$sHtml .= "</td></tr>";
		$sHtml .= "</table>";
		
		$sHtml .= $this->getMailHTMLBottom(false);

		return $sHtml;
	}
	
	/**
	* Get HTML for sending init concert mail.
	*/
	public function getInitConcertMailHTML($aData, $display_name) {
		$sHtml = "";
		//var_dump($aData);
		if (count($aData) > 0) {
			$sHtml .= $this->getMailHTMLTop();
			
			// Main content
			$sHtml .= "<table cellpadding='0' cellspacing='10' bgcolor='ffffff' width='600' align='center'>";
			$sHtml .= "<tr><td>";
			// Inner content top text
			$sHtml .= "<table border='0' cellpadding='10' cellspacing='0' width='500' align='center' style='font-family:Helvetica,sans-serif,Arial;'>";
			$sHtml .= "<tr><td style='font-size:20px;font-weight:bold;'>Kære {$display_name}</td></tr>";
			$sHtml .= "<tr><td style='font-size:14px;'>Din Koncert Agent er nu oprettet.</td></tr>";
			$sHtml .= "<tr><td style='font-size:14px;'>Du vil fremover få besked når vi finder koncerter der matcher din Agent.</td></tr>";
			$sHtml .= "<tr><td style='font-size:14px;'>Nedenfor finder du listen med allerede kendte koncerter.</td></tr>";
			$sHtml .= "</table>";
			
			// Inner content prices.
			$sHtml .= "<table cellpadding='3' cellspacing='0' width='500' align='center' style='font-size:12px;font-family:Helvetica,sans-serif,Arial;' border='0'>";
			
			$sHtml .= "<tr><td style='font-size:18px;line-height:28px;height:28px;font-weight:bold;background-color:#272221;color:#ffffff;' colspan='4'>" . $aData[0]["artist_name"] . "</td></tr>";
			$item_count = 1;
			foreach ($aData AS $a) {
				$event_date = ($a["item_event_date"] == "0000-00-00") ? "&nbsp;" : $a["item_event_date"];
				$event_time = ($a["item_event_time"] == "00:00:00") ? "&nbsp;" : $a["item_event_time"];
				$price_local = ((int)$a["price_local"] == 1) ? "&nbsp;" : ceil($a["price_local"]) . " DKK";
				$aContent = array("item_name" => $a["item_price_name"], "item_event_date" => $event_date, "item_event_time" => $event_time, "price_local" => $price_local);
				$sHtml .= $this->GetTableRowHTML($aContent, $item_count);
				$item_count++;
			}
			$sHtml .= "</table>";
			$url = "http://www.airplaymusic.dk/kunstner/" . $this->airplay_name_to_url($aData[0]["artist_name"]) . "?media_format=ALL&product=concert";

			// Inner content links and stuff
			$sHtml .= "<table border='0' cellpadding='10' cellspacing='0' width='500' align='center' style='font-family:Helvetica,sans-serif,Arial;'>";
			$sHtml .= "<tr><td style='font-size:20px;font-weight:bold;' align='center'><a href='{$url}' target='_blank' style='color:#E55D09;'>Læs mere på Airplay Music</a></td></tr>";
			$sHtml .= "</table>";
			
			$sHtml .= "</td></tr>";
			$sHtml .= "</table>";
			
		 	$sHtml .= $this->getMailHTMLBottom(true);
		}
		return $sHtml;
	}
	

	/**
	* Get HTML for sending init concert mail.
	*/
	public function getConcertMailHTML($aData, $display_name) {
		$sHtml = "";
		if (count($aData) > 0) {
			$sHtml .= $this->getMailHTMLTop();
			
			// Main content
			$sHtml .= "<table cellpadding='0' cellspacing='10' bgcolor='ffffff' width='600' align='center'>";
			$sHtml .= "<tr><td>";
			// Inner content top text
			$sHtml .= "<table border='0' cellpadding='10' cellspacing='0' width='500' align='center' style='font-family:Helvetica,sans-serif,Arial;'>";
			$sHtml .= "<tr><td style='font-size:20px;font-weight:bold;'>Kære {$display_name}</td></tr>";
			$sHtml .= "<tr><td style='font-size:14px;'>Din Koncert Agent har fundet nye koncerter:</td></tr>";
			$sHtml .= "</table>";
			
			// Inner content prices.
			$sHtml .= "<table cellpadding='3' cellspacing='0' width='500' align='center' style='font-size:12px;font-family:Helvetica,sans-serif,Arial;' border='0'>";
			
			$sHtml .= "<tr><td style='font-size:18px;line-height:28px;height:28px;font-weight:bold;background-color:#272221;color:#ffffff;' colspan='4'>" . $aData[0]["artist_name"] . "</td></tr>";
			$item_count = 1;
			foreach ($aData AS $a) {
				$event_date = ($a["item_event_date"] == "0000-00-00") ? "&nbsp;" : $a["item_event_date"];
				$event_time = ($a["item_event_time"] == "00:00:00") ? "&nbsp;" : $a["item_event_time"];
				$price_local = ((int)$a["price_local"] == 1) ? "&nbsp;" : ceil($a["price_local"]) . " DKK";
				$aContent = array("item_name" => $a["item_price_name"], "item_event_date" => $event_date, "item_event_time" => $event_time, "price_local" => $price_local);
				$sHtml .= $this->GetTableRowHTML($aContent, $item_count);
				$item_count++;
			}
			$sHtml .= "</table>";
			$url = "http://www.airplaymusic.dk/kunstner/" . $this->airplay_name_to_url($aData[0]["artist_name"]) . "?media_format=ALL&product=concert";

			// Inner content links and stuff
			$sHtml .= "<table border='0' cellpadding='10' cellspacing='0' width='500' align='center' style='font-family:Helvetica,sans-serif,Arial;'>";
			$sHtml .= "<tr><td style='font-size:20px;font-weight:bold;' align='center'><a href='{$url}' target='_blank' style='color:#E55D09;'>Læs mere på Airplay Music</a></td></tr>";
			$sHtml .= "</table>";
			
			$sHtml .= "</td></tr>";
			$sHtml .= "</table>";
			
		 	$sHtml .= $this->getMailHTMLBottom(true);
		}
		return $sHtml;
	}
	
	/**
	* Get HTML for sending album max price
	*/
	public function getMaxPriceAlbumMailHTML($aData, $display_name) {
		$sHtml = "";
		if (count($aData) > 0) {
			$sHtml .= $this->getMailHTMLTop();
			
			// Main content
			$sHtml .= "<table cellpadding='0' cellspacing='10' bgcolor='ffffff' width='600' align='center'>";
			$sHtml .= "<tr><td>";
			// Inner content top text
			$sHtml .= "<table border='0' cellpadding='10' cellspacing='0' width='500' align='center' style='font-family:Helvetica,sans-serif,Arial;'>";
			$sHtml .= "<tr><td style='font-size:20px;font-weight:bold;'>Kære {$display_name}</td></tr>";
			$sHtml .= "<tr><td style='font-size:14px;'>Din Album Agent har fundet nye priser:</td></tr>";
			$sHtml .= "</table>";
			
			// Inner content prices.
			$sHtml .= "<table cellpadding='3' cellspacing='0' width='500' align='center' style='font-size:12px;font-family:Helvetica,sans-serif,Arial;' border='0'>";
			$sHtml .= "<tr><td style='font-size:18px;line-height:28px;height:28px;font-weight:bold;background-color:#272221;color:#ffffff;' colspan='4'>" . $aData[0]["artist_name"] . " - " . $aData[0]["item_base_name"] . "</td></tr>";
			$item_count = 1;
			foreach ($aData AS $a) {
				$media_format_id = $a["media_format_id"];
				if ($media_format_id == 3) {
					$media_format_name = "MP3";
				} else if ($media_format_id == 5) {
					$media_format_name = "CD";
				} else if ($media_format_id == 7) {
					$media_format_name = "Vinyl";
				} else if ($media_format_id == 8) {
					$media_format_name = "DVD";
				} else if ($media_format_id == 10) {
					$media_format_name = "Blu-ray";
				} else if ($media_format_id == 16) {
					$media_format_name = "FLAC";
				}
				
				$aContent = array("item_name" => $a["item_price_name"], "media_format_id" => $media_format_name, "price_local" => $a["price_local"] . " DKK");
				$sHtml .= $this->GetTableRowHTML($aContent, $item_count);
				$item_count++;
			}
			$sHtml .= "</table>";
			$url = "http://www.airplaymusic.dk/kunstner/" . $this->airplay_name_to_url($aData[0]["artist_name"]) . "/album/" . $this->airplay_name_to_url($aData[0]["item_base_name"]);

			// Inner content links and stuff
			$sHtml .= "<table border='0' cellpadding='10' cellspacing='0' width='500' align='center' style='font-family:Helvetica,sans-serif,Arial;'>";
			$sHtml .= "<tr><td style='font-size:20px;font-weight:bold;' align='center'><a href='{$url}' target='_blank' style='color:#E55D09;'>Læs mere på Airplay Music</a></td></tr>";
			$sHtml .= "</table>";
			
			$sHtml .= "</td></tr>";
			$sHtml .= "</table>";
			
		 	$sHtml .= $this->getMailHTMLBottom(true);
		}
		return $sHtml;
	}
	
	/**
	* Get HTML for price agent top incl wrapper
	*/
	private function getMailHTMLTop() {
		$sHtml = "";
		$sHtml .= "<html><p><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'><title></title></head></p>";
		$sHtml .= "<p><body bgcolor='EFEFEF'></p>";
		// Wrapper for the entire mail
		$sHtml .= "<p><table width='100%' cellpadding='0' cellspacing='0' bgcolor='EFEFEF' border='0' ><tr><td></p>";
		// HTML top
		$sHtml .= "<p><table cellpadding='0' cellspacing='0' width='600' align='center' bgcolor='272221' border='0' >";
		$sHtml .= "<tr><td style='border-bottom:5px solid #E55D09;'><img src='http://static.airplay-music.dk/images/site/logo.png' width='364' height='75' border='0' /> </td></tr>";
		$sHtml .= "</table>";
		return $sHtml;
	}
	
	/**
	* Get HTML for price agent bottom incl wrapper
	*/
	private function getMailHTMLBottom($unsubscribe_link) {
		$sHtml = "";
		$sHtml .= "<table cellpadding='0' cellspacing='0' width='600' align='center' bgcolor='272221' border='0' style='font-family:Helvetica,sans-serif,Arial;'>";
		//$sHtml .= "<tr><td style='border-top:5px solid #E55D09;color:#FFFFFF;font-size:11px;' align='center'>Du modtager denne mail, da du .................</td></tr>";
		$sHtml .= "<tr><td style='border-top:5px solid #E55D09;color:#FFFFFF;font-size:11px;' align='center'>&nbsp;</td></tr>";
		$sHtml .= "<tr><td style='color:#FFFFFF;font-size:11px;' align='center'>&nbsp;</td></tr>";
		if ($unsubscribe_link == true) {
			$sHtml .= "<tr><td style='color:#FFFFFF;font-size:11px;' align='center'><a href='http://www.airplaymusic.dk/bruger-indstillinger' target='_blank' style='color: #E55D09;'>Rediger eller afmeld denne Agent via bruger indstillinger</a></td></tr>";
			$sHtml .= "<tr><td style='color:#FFFFFF;font-size:11px;' align='center'>&nbsp;</td></tr>";
		}
		$sHtml .= "</table></p>";
		// Wrapper end
		$sHtml .= "<p></tr></td></table></p>";
		$sHtml .= "<p></body></p><p></html></p>";
		return $sHtml;
	}
	
	/**
	* Get HTML for price agent table row
	*/
	private function GetTableRowHTML($aData, $item_count) {
		if ($item_count%2 == 1) { $style = "style='background-color:#EEEEEE;border-bottom: 1px solid #CCCCCC;padding: 0.1em 0.6em;'"; } else { $style = "style='background-color:#FFFFFF;border-bottom: 1px solid #CCCCCC;padding: 0.1em 0.6em;'"; }
		$sHtml = "<tr $style>";
		foreach ($aData AS $a) {
			$sHtml .= $this->GetTableDataHTML($a);
		}
		$sHtml .= "</tr>";
		return $sHtml;
	}
	
	/**
	* Get HTML for price agent table data
	*/
	private function GetTableDataHTML($text) {
		$sHtml = "";
		// HAAAAAACKKKK
		if (strlen($text) == 10 && is_numeric(substr($text, 0, 4)) && is_numeric(substr($text, -2)) ) {
			$text = $this->GetFormattedDate($text, 'da');
		}
		$sHTML .= "<td>{$text}</td>";
		return $sHTML;
	}
	
	private function airplay_name_to_url( $sName ) 
	{
		$G_URL_TO_NORMAL_fromUrl    =  array("_"   , "-AND-", "-SLASH-", "-QMARK-", "-PERCENT-", "-PLUS-"  );
		$G_URL_TO_NORMAL_toNormal   =  array(" "    , "&"   , "/"      , "?"  , "%"  , "+"    );
		$sUrl = str_replace  ( $G_URL_TO_NORMAL_toNormal, $G_URL_TO_NORMAL_fromUrl, $sName );	
		return $sUrl;
	}
	
	
	/**
	*	Function used to format date.
	*/
	private function GetFormattedDate($date, $language_code) {
		if ($language_code == "da") {
			return substr($date, -2) . "." . substr($date, 5, 2) . "." . substr($date, 0, 4);
		} else {
			return $date;
		}
	}

	public function formatText($text, $artist_name, $user_name, $content) {
//		if ($text_type == 1 && $text != "") { // HTML text
		$s = str_replace("[CONTENT]", $content, str_replace("[USER_NAME]", $user_name, str_replace("[ARTIST_NAME]", $artist_name, $text)));
	//	} // SMS text
		return $s;
	}
	
	
	public function ap_sendmail($user_name, $user_email, $text, $send_alternative_text, $subject) {

		$ap_mail = new AirplayMusicMailer();
		$ap_mail->SetSender();
		$ap_mail->AddAddress($user_email, $user_name);
		$ap_mail->AddReplyTo($this->From, $this->ap_sender_name);
		$ap_mail->Subject  		= $subject;
		$ap_mail->Body     		= $text;
		$ap_mail->AltBody 		= $send_alternative_text;
		$ap_mail->WordWrap 	= 50;
		
		if(!$ap_mail->Send()) {
		  echo 'Message was not sent.\n';
		  echo 'Mailer error: ' . $ap_mail->ErrorInfo . "\n";
		} else {
			//echo 'Message has been sent.\n';
		}
		
	}
	
	
	// -----------------------
    // --- PUBLIC: Data --- 
    // -----------------------
	
	public      $m_dbPDO;
	public 		$artist_id = 0;
	public		$agent_id = 0;
	public 		$user_name = "";
	public		$user_email = "";
	public		$timestamp_added = "";
	public		$item_base_id = 0;
	public		$max_price = 0;
}
?>