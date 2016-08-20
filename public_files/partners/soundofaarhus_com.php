<?php
 
    if (isset($_GET["skey"]) && $_GET["skey"] == "aed6388057755d62d357bff5c2899b81") {
        include "../../drupal7/sites/all/modules/airplay_base/airplay_base_url_functions.inc";
        include "../../filesupload/public_html/include_db_functions.php";
		if (isset($_GET["type"]) && $_GET["type"] == "text") {
			$format = "text";
		} else {
			$format = "json";
		}
		if ($format == "json") {
			header('Content-Type: text/json');
		} else {
			print '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN"  "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd"><html lang="en"><head><meta charset="utf-8" /><body>';
		}
        
        $dbconnection = open_db_v1();
        $undertoner_links = array();

        $sql = "SELECT artist_name, item_base_name, soundofaarhus_review_url FROM item_base INNER JOIN artist ON artist.artist_id = item_base.artist_id WHERE soundofaarhus_review_url <> ''";
        $undertoner_data = sql_return_query($dbconnection, $sql, array());

        if (is_array($undertoner_data)) {
            $item_array_size = sizeof($undertoner_data);
            if ($item_array_size > 0) {
                for ($i = 0; $i < $item_array_size; $i++)
                {
                    extract($undertoner_data[$i]);
                    $ap_link_dk = "http://www.airplaymusic.dk/kunstner/" . airplay_name_to_url($artist_name) . "/album/" . airplay_name_to_url($item_base_name);
                    
                    $array = array('ap_artist_name' => $artist_name, 'ap_album_name' => $item_base_name, 'ap_link_dk' => $ap_link_dk, 'soa_link' => $soundofaarhus_review_url);
                    $undertoner_links[] = $array;
                }
            }
        }
        close_db($dbconnection);
		
        if ($format == "json") {
			$result = array("result" => $undertoner_links);
			print json_encode($undertoner_links, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		} else {
			foreach($undertoner_links AS $a) {
				print "<b>Kunstner:</b> " . $a["ap_artist_name"] . " <b>Album:</b> " . $a["ap_album_name"] . " <b>Airplay Music link:</b> <a href='" . $a["ap_link_dk"]  . "' target='_blank'>" . $a["ap_link_dk"] . "</a> <b>Sound of Aarhus link:</b> <a href='" . $a["soa_link"]  . "' target='_blank'>" . $a["soa_link"] . "</a><br>";
			}
			print "</body></html>";
		}
        
    } else {
        print "<h1>Airplay Music</h1>";
        print "<p>Incorrect key</p>";
    }
?>
