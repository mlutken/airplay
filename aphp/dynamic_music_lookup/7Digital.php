<?php

/*
	READ ME:
	http://blogs.7digital.com/dev/2013/05/21/json-is-coming/
	
	http://api.7digital.com/1.2/artist/browse?letter=pink%20floyd&oauth_consumer_key=7d5w2stt2zk5&country=GB - artist ids
	http://api.7digital.com/1.2/artist/releases?artistid=447&oauth_consumer_key=7d5w2stt2zk5&country=GB&pagesize=500&type=album - albums
*/

require_once ("dynamic_music_lookup/BaseDynamicMusic.php");


class SevenDigital extends BaseDynamicMusic
{
    public function __construct( )
    {
        parent::__construct();
        parent::setSearchForCountryCode();
        parent::setRecordStoreID("7Digital (UK)");
        parent::setRecordStoreData("7Digital (UK)");
        parent::setSearchForArtistname();
        parent::setSearchForArtistID();
        parent::setSearchForItemBaseID();
        parent::setSearchForType();
        parent::setSearchForItemname();
		$this->oauth_consumer_key = "7d5w2stt2zk5";
    }
	
    public function getDataFromWebservice()
    {
        $masterresults = array();
		$results = array();
		$tracks = array();
		$track = array();
        $artist_id = 0;

		/*******
			Get artist ID
		******/
		// Url Encode for at man kan søge på "Johnny Madsen" - dette skal være et UTF-8 format.
		// Bug virker ikke med Lis Sørensen.
		$artist_url = "http://api.7digital.com/1.2/artist/browse?letter=" . urlencode($this->search_for_artist) . "&oauth_consumer_key=" . $this->oauth_consumer_key;
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $artist_url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
		$masterresults = curl_exec($ch);
		curl_close($ch);
		
		$artists = json_decode($masterresults, true);

		/* Get Artist id */
		if (count($artists["artists"]["artist"]) > 0) {
			for ($i=0;$i <= count($artists["artists"]["artist"]); $i++) {
				if (strtolower($artists["artists"]["artist"][$i]["name"]) == strtolower($this->search_for_artist)) {
					$artist_id = $artists["artists"]["artist"][$i]["id"];
					break;
				};
			}
		}
		// Make sure that we have an artist.
		if ($artist_id != 0) {
			// Get all albums for an artist - removing albums in next function.
			if ($this->search_for_type == "artist_album" || $this->search_for_type == "album") {

				if ($artist_id != 0) {
					//&country={$lang}
					$this->record_store_webservice_url = "http://api.7digital.com/1.2/artist/releases?artistid={$artist_id}&type=album&oauth_consumer_key={$this->oauth_consumer_key}&pagesize=500";

					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $this->record_store_webservice_url);
					curl_setopt($ch, CURLOPT_HEADER, false);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
					$results = curl_exec($ch);
					curl_close($ch);
					$results = json_decode($results, true);
					$results =  $results["releases"]["release"];
				}
			// Get all albums for an artist - removing albums in next function.
			} else if ($this->search_for_type == "artist_song" ) {
				if ($artist_id != 0) {
					//&country={$lang}
					$this->record_store_webservice_url = "http://api.7digital.com/1.2/artist/releases?artistid={$artist_id}&type=album&oauth_consumer_key={$this->oauth_consumer_key}&pagesize=500";

					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $this->record_store_webservice_url);
					curl_setopt($ch, CURLOPT_HEADER, false);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
					$albums = curl_exec($ch);
					curl_close($ch);

					$albums = json_decode($albums, true);
					$albums =  $albums["releases"]["release"];

					// Get all tracks for each album.
					$album_count =  count($albums);
					for ($i = 0; $i <= $album_count; $i++) {
						//&country={$lang}
						$track_url = "http://api.7digital.com/1.2/release/tracks?releaseid=" . $albums[$i]["id"] . "&oauth_consumer_key={$this->oauth_consumer_key}&pagesize=50";

						$ch = curl_init();
						curl_setopt($ch, CURLOPT_URL, $track_url);
						curl_setopt($ch, CURLOPT_HEADER, false);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						// ONLY XML RESULT IN API FOR NOW..........
						//curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
						$tracks = curl_exec($ch);
						curl_close($ch);
						$tracks = $this->xml2array($tracks);
						$tracks = $tracks[0];

						$track_count = count($tracks);
						for ($j = 0; $j <= $track_count; $j++) {
							if (count($tracks[$j]) == 14) {
								$item_type = strtolower($tracks[$j]["tag"]);
								$track_name = $tracks[$j][0]["value"];
								$artist_name = $tracks[$j][2][0]["value"];
								$track_url = $tracks[$j][9]["value"];
								$item_price = $tracks[$j][10][1]["value"];
								$item_currency = $tracks[$j][10][0]["attributes"]["CODE"];
								if ($track_name != "" && $track_url != "" && $artist_name != "" && $item_price != "" && $item_currency != "" ) {
									$track[] = array("track_name" => $track_name , "artist_name" => $artist_name , "track_url" => $track_url , "item_price" => $item_price , "item_currency" => $item_currency, "type" => $item_type);
								}
							}
						}
					}
					$results = $track;
				}
			} else if ($this->search_for_type == "song") {
				
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $this->record_store_webservice_url);
				curl_setopt($ch, CURLOPT_HEADER, false);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
				$results = curl_exec($ch);
				curl_close($ch);
				$results = json_decode($results, true);
				$results = $results["searchResults"]["searchResult"];
			}
		}
        return $results;
    }
	

    public function parseDataFromWebservice($result, &$a_item_names)
    {
  
		//print "<pre>";
    	//var_dump($result);
        $all_items_count = 0;
        $response = array();
        $response_collapsed = array();
        $response_all_items = array();
        $item_list = array();   // Array used to limit number of album/song - to only list same album/song once.
        $valid_items = array(); // Array used to names of valid items - like counting number of albums/songs for an artist.

        if (count($result) > 0) {
            foreach ($result AS $data) {
                if ($this->search_for_type == "artist_album") {
                        /* Convert item_name to collapsed names -  used on artist_page */
                        $item_name = $data["title"];
                        $item_name = cleanItemNameFull($item_name, $m_dbAll->m_dbItemBaseCorrection);
                        $item_name = parent::removeRecordSpecificTextFromItem($item_name);
                        /* Make sure that we only add albums, songs that we have in item_base */
                        if ($data["type"] == "Album" && $this->isItemNameFoundInMasterData($item_name, $a_item_names) == true && in_array($item_name, $item_list) == false) {
                            $item = array("artist_name" => $data["artist"]['name'], "item_name" => $item_name, "buy_at_url" => $data["url"] , "affiliate_url" => $this->record_store_affiliate_url, "affiliate_encode_times" => $this->record_store_affiliate_encode_times, "price_local" => ($data["price"]["value"]*100), "currency_code" => $data["price"]["currency"]["code"], "media_format_name" => "MP3", "record_store_name" => "7Digital (UK)", "record_store_class_name" => "sevendigital");
                            $response_collapsed["items"][] = array("item" => $item);
                            $item_list[] = $item_name;
                            $valid_items[] = $item_name;
                        }
                        $all_item = array("artist_name" => $data['artistName'], "item_name" => $item_name, "media_format_name" => "MP3");
                        $response_all_items["items"][] = array("item" => $all_item);
                        $all_items_count++;
                } else if ($this->search_for_type == "artist_song") {
                    /* Convert item_name to collapsed names -  used on artist_page */
                    $item_name = $data["track_name"];
                    $item_name = cleanItemNameFull($item_name, $m_dbAll->m_dbItemBaseCorrection);
                    $item_name = parent::removeRecordSpecificTextFromItem($item_name);
                    /* Make sure that we only add albums, songs that we have in item_base */
                    if ($data["type"] == "track" && $this->isItemNameFoundInMasterData($item_name, $a_item_names) == true && in_array($item_name, $item_list) == false) {
                        $item = array("artist_name" => $data['artist_name'], "item_name" => $item_name, "buy_at_url" => $data["track_url"] , "affiliate_url" => $this->record_store_affiliate_url, "affiliate_encode_times" => $this->record_store_affiliate_encode_times, "price_local" => ($data["item_price"]*100), "currency_code" => $data["item_currency"], "media_format_name" => "MP3", "record_store_name" => "7Digital (UK)", "record_store_class_name" => "sevendigital");
                        $response_collapsed["items"][] = array("item" => $item);
                        $item_list[] = $item_name;
                        $valid_items[] = $item_name;
                    }
                    $all_item = array("artist_name" => $data['artistName'], "item_name" => $item_name, "media_format_name" => "MP3");
                    $response_all_items["items"][] = array("item" => $all_item);
                    $all_items_count++;
                } else if ($this->search_for_type == "album") {
                    $artist_name = $data["artist"]['name'];
                    $item_name = $data['title'];
                    if ($data["type"] == "Album" && strtolower($artist_name) == strtolower($this->search_for_artist) && $data["price"]["value"] > 0 && $this->isItemNameInBeginningOfMasterData($item_name, $a_item_names) == true && in_array($item_name, $item_list) == false) {
                        $item = array("artist_name" => $artist_name, "item_name" => $item_name, "buy_at_url" => $data["url"]   , "affiliate_url" => $this->record_store_affiliate_url, "affiliate_encode_times" => $this->record_store_affiliate_encode_times, "price_local" => ($data["price"]["value"]*100), "currency_code" => $data["price"]["currency"]["code"], "media_format_name" => "MP3", "record_store_name" => "7Digital (UK)", "record_store_class_name" => "sevendigital");
                        $response["items"][] = array("item" => $item);
                        $valid_items[] = $item_name;
						$item_list[] = $item_name;
                    }
                } else if ($this->search_for_type == "song") {
                    $artist_name = $data['track']["artist"]["name"];
                    $item_name = $data["track"]["title"];
                    if ($data["type"] == "track" && strtolower($artist_name) == strtolower($this->search_for_artist) && in_array($item_name, $item_list) == false && $this->isItemNameInBeginningOfMasterData($item_name, $a_item_names) == true) {
                        $item = array("artist_name" => $artist_name, "item_name" => $item_name, "buy_at_url" => $data["track"]["url"] , "affiliate_url" => $this->record_store_affiliate_url, "affiliate_encode_times" => $this->record_store_affiliate_encode_times, "price_local" => ($data["track"]["price"]["value"]*100), "currency_code" => $data["track"]["price"]["currency"]["code"], "media_format_name" => "MP3", "record_store_name" => "7Digital (UK)", "record_store_class_name" => "sevendigital");
                        $response["items"][] = array("item" => $item);
                        $valid_items[] = $item_name;
                        $item_list[] = $item_name;
                    }
                }

            }
        }
        
        if (count($valid_items) > 0) {
            if ($this->search_for_type == "artist_album") {
                $valid_items = array_unique($valid_items);
                $response_collapsed["item_count"][] = array("name" => "7Digital", "type" => "album", "item_count" => count($valid_items));
                $response_all_items["item_count"][] = array("name" => "7Digital", "type" => "album", "item_count" => $all_items_count);
            } else if ($this->search_for_type == "artist_song") {
                $valid_items = array_unique($valid_items);
                $response_collapsed["item_count"][] = array("name" => "7Digital", "type" => "song", "item_count" => count($valid_items));
                $response_all_items["item_count"][] = array("name" => "7Digital", "type" => "song", "item_count" => $all_items_count);
            } else {
                $response["item_count"][] = array("name" => "7Digital", "type" => $this->search_for_type, "item_count" => count($valid_items));
            }
        } else {
            if ($this->search_for_type == "artist_album") {
                $response_all_items["item_count"][] = array("name" => "7Digital", "type" => "album", "item_count" => $all_items_count);
            } else if ($this->search_for_type == "artist_song") {
                $response_all_items["item_count"][] = array("name" => "7Digital", "type" => "song", "item_count" => $all_items_count);
            }
        }

        return array("response" => $response, "response_collapsed" => $response_collapsed, "response_all_items" => $response_all_items, "item_count" => count($valid_items));

    }

    /*
        7Digital used "GB" for "UK" - hacked "$lang".
    */
    
    public function setWebserviceURL()
    {
        if ($this->record_store_webservice_country == "DK") {
            $lang = "DK";
        } else {
            $lang = "GB";
        }
        if ($this->search_for_type == "artist_album") {
            $this->record_store_webservice_url = "";
        } else if ($this->search_for_type == "artist_song") {
            $this->record_store_webservice_url = "";
        } else if ($this->search_for_type == "album") {
            $this->record_store_webservice_url = "";
        } else if ($this->search_for_type == "song") {
			$this->record_store_webservice_url = "http://api.7digital.com/1.2/track/search?q=" . rawurlencode($this->search_for_words) . "%20" . rawurlencode($this->search_for_artist) . "&oauth_consumer_key={$this->oauth_consumer_key}&country={$lang}&pagesize=500";
        }
    }

	/* 7Digital specific function */
	function xml2array($xml){ 
		$opened = array(); 
		$opened[1] = 0; 
		$xml_parser = xml_parser_create(); 
		xml_parse_into_struct($xml_parser, $xml, $xmlarray); 
		$array = array_shift($xmlarray); 
		unset($array["level"]); 
		unset($array["type"]); 
		$arrsize = sizeof($xmlarray); 
		for($j=0;$j<$arrsize;$j++){ 
			$val = $xmlarray[$j]; 
			switch($val["type"]){ 
				case "open": 
					$opened[$val["level"]]=0; 
				case "complete": 
					$index = ""; 
					for($i = 1; $i < ($val["level"]); $i++) 
						$index .= "[" . $opened[$i] . "]"; 
					$path = explode('][', substr($index, 1, -1)); 
					$value = &$array; 
					foreach($path as $segment) 
						$value = &$value[$segment]; 
					$value = $val; 
					unset($value["level"]); 
					unset($value["type"]); 
					if($val["type"] == "complete") 
						$opened[$val["level"]-1]++; 
				break; 
				case "close": 
					$opened[$val["level"]-1]++; 
					unset($opened[$val["level"]]); 
				break; 
			} 
		} 
		return $array; 
	} 
	
    public       $artist_id;
    public       $search_for_words;
    public       $search_for_artist;
    public		$search_for_type;
    public		$search_for_type_id;
    public       $item_base_id;
    public       $record_store_id;
    public		$record_store_webservice_country;
    public       $record_store_webservice_url;
    public       $record_store_affiliate_url;
    public       $record_store_affiliate_encode_times;
    public       $m_dbPDO;
	// 7Digital specific variable.
	public 		$oauth_consumer_key;
}


?>