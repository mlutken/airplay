<?php

require_once ("dynamic_music_lookup/BaseDynamicMusic.php");


class Napster extends BaseDynamicMusic
{

    public function __construct( )
    {
        parent::__construct();
        parent::setRecordStoreID("Napster (UK)");
        parent::setRecordStoreData("Napster (UK)");
        parent::setSearchForArtistname();
        parent::setSearchForArtistID();
        parent::setSearchForItemBaseID();
        parent::setSearchForType();
        parent::setSearchForItemname();
        parent::setSearchForCountryCode();
                        
    }
	
    public function getDataFromWebservice()
    {

        /* Find all songs */
        if ($this->search_for_type == "song") {
            if ($this->record_store_webservice_country == "DK") {
                $tracks_url = "http://api.rhapsody.com/v1/search?q=" . urlencode($this->search_for_words) . "&type=track&limit=100&apikey=GZcG44guuRhKRlaZqHVAYUxW6I8xrGSf&catalog=DK";
            } else {
                $tracks_url = "http://api.rhapsody.com/v1/search?q=" . urlencode($this->search_for_words) . "&type=track&limit=100&apikey=GZcG44guuRhKRlaZqHVAYUxW6I8xrGSf&catalog=GB";
            }
            $results = file_get_contents( $tracks_url );
        /* Find artist_albums, artist_songs, albums */
        } else if ($this->search_for_type == "artist_album" || $this->search_for_type == "artist_song" || $this->search_for_type == "album") {

            $artist_id = "";
            $artist_url = "http://api.rhapsody.com/v1/search/typeahead?q=" . urlencode($this->search_for_artist) . "&type=artist&apikey=GZcG44guuRhKRlaZqHVAYUxW6I8xrGSf";

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $artist_url);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array());
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
			$file_content = curl_exec($ch);
            $result = json_decode($file_content, true);

            /* Get Artist id */
            if (count($result) > 0) {
                for ($i=0;$i <= count($result); $i++) {
                    if (strtolower($result[$i]["name"]) == strtolower($this->search_for_artist)) {
                        $artist_id = $result[$i]["id"];
                        break;
                    };
                }
            }

            if ($artist_id != "") {
                if ($this->record_store_webservice_country == "DK") {
                    $url = "http://api.rhapsody.com/v1/artists/" . $artist_id. "/albums?apikey=GZcG44guuRhKRlaZqHVAYUxW6I8xrGSf&limit=100&catalog=DK";
                } else {
                    $url = "http://api.rhapsody.com/v1/artists/" . $artist_id. "/albums?apikey=GZcG44guuRhKRlaZqHVAYUxW6I8xrGSf&limit=100&catalog=GB";
                }
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array());
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
                $results = curl_exec($ch);
                curl_close($ch);
            }
        }
        return json_decode($results,true);
    }
	
    public function parseDataFromWebservice($result, &$a_item_names)
    {
        $response = array();
        $response_collapsed = array();
        $item_list = array();   // Array used to limit number of album/song - to only list same album/song once.
        $valid_items = array(); // Array used to names of valid items - like counting number of albums/songs for an artist.
        $album_ids = ""; // Album ids to lookup.
        $track_ids = ""; // Track ids to lookup.
        /* get albums for fewer requests */
        if ($this->search_for_type == "artist_album") {
            if (count($result) > 0) {
                for ( $i=0; $i <= count($result); $i++) {
                    if ($result[$i]["type"]["id"] == 0 && strtolower($result[$i]["artist"]["name"]) == strtolower($this->search_for_artist)) {
                        $album_ids .= "&ids=". $result[$i]["id"];
                    }
                }
                $ch = curl_init();
                $url = $this->record_store_webservice_url . $album_ids;
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array());
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
                $results = curl_exec($ch);
                curl_close($ch);
                $albums = json_decode($results,true);
            }
        }
        
        if (count($result) > 0) {
            for ( $i=0; $i <= count($result); $i++) {
                if ($this->search_for_type == "artist_album") {
                    $artist_name = $result[$i]["artist"]["name"];
                    // ID 0 = Main releases
                    // ID 1 = EP
                    if ($result[$i]["type"]["id"] == 0 && strtolower($artist_name) == strtolower($this->search_for_artist)) {
                        /* Convert item_name to collapsed names -  used on artist_page */
                        //$item_name = parent::removeRecordSpecificTextFromItem($item_name);
                        $item_name = $result[$i]["name"];
                        $valid_items[] = $item_name;
                        $item_name = cleanItemNameFull($item_name, $m_dbAll->m_dbItemBaseCorrection);
                        if (count($albums) > 0) {
                            for ( $j = 0; $j <= count($albums); $j++) {
                                if ($result[$i]["id"] == $albums[$j]["id"]) {
                                    $buy_at_url = "http://www.napster.com/artist/" . $albums[$j]["tokens"]["artist"] . "/album/" . $albums[$j]["tokens"]["album"];
                                }
                            }
                        }
                        /* Make sure that we only add albums, songs that we have in item_base */
                        if ($this->isItemNameFoundInMasterData($item_name, $a_item_names) == true && in_array($item_name, $item_list) == false) {
                            $item = array("artist_name" => $artist_name, "item_name" => $item_name, "buy_at_url" => $buy_at_url, "price_local" => 0, "currency_code" => "DKK", "media_format_name" => "Streaming", "record_store_name" => "Napster (" . $this->record_store_webservice_country . ")", "record_store_class_name" => "napster");
                            $response_collapsed["items"][] = array("item" => $item);
                            $item_list[] = $item_name;
                        }
                    }
                } else if ($this->search_for_type == "artist_song") {
                    /* Convert item_name to collapsed names -  used on artist_page */
                    //$item_name = parent::removeRecordSpecificTextFromItem($item_name);
                    $artist_name = $result[$i]["artist"]["name"];
                    // ID 0 = Main releases
                    // ID 1 = EP
                    if (strtolower($artist_name) == strtolower($this->search_for_artist)) {
                        /* GET URL */
                        $ch = curl_init();
                        //$url = "http://api.rhapsody.com/v1/albums/" . $result[$i]["id"] . "/tracks?apikey=GZcG44guuRhKRlaZqHVAYUxW6I8xrGSf";
                        if ($this->record_store_webservice_country == "DK") {
                            $url = "http://api.rhapsody.com/v1/albums/" . $result[$i]["id"] . "/tracks?apikey=GZcG44guuRhKRlaZqHVAYUxW6I8xrGSf&catalog=DK";
                        } else {
                            $url = "http://api.rhapsody.com/v1/albums/" . $result[$i]["id"] . "/tracks?apikey=GZcG44guuRhKRlaZqHVAYUxW6I8xrGSf&catalog=GB";
                        }
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_HEADER, false);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array());
						curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
                        $tracks = curl_exec($ch);
                        curl_close($ch);
                        $tracks = json_decode($tracks,true);

                        for ($track = 0; $track <= count($tracks); $track++) {
                            if (strtolower($tracks[$track]["artist"]["name"]) == strtolower($this->search_for_artist)) {
                                $track_ids .= "&ids=". $tracks[$track]["id"];
                            }
                        }

                        $ch = curl_init();
                        $url = $this->record_store_webservice_url . $track_ids;
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_HEADER, false);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array());
						curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
                        $results = curl_exec($ch);
                        curl_close($ch);
                        $albums = json_decode($results,true);
                        for ($track = 0; $track <= count($tracks); $track++) {
                            $item_name = $tracks[$track]["name"];
                            $valid_items[] = $item_name;
                            $item_name = cleanItemNameFull($item_name, $m_dbAll->m_dbItemBaseCorrection);
                            if (count($albums) > 0) {
                                for ( $j = 0; $j <= count($albums); $j++) {
                                    if ($albums[$j]["id"] == $tracks[$track]["id"]) {
                                        $buy_at_url = "http://www.napster.com/artist/" . $albums[$j]["tokens"]["artist"] . "/album/" . $albums[$j]["tokens"]["album"] . "/track/" . $albums[$j]["tokens"]["track"];
                                    }
                                }
                            }
                            /* Make sure that we only add albums, songs that we have in item_base */
                            if ($this->isItemNameFoundInMasterData($item_name, $a_item_names) == true && in_array($item_name, $item_list) == false) {
                                $item = array("artist_name" => $artist_name, "item_name" => $item_name, "buy_at_url" => $buy_at_url, "price_local" => 0, "currency_code" => "DKK", "media_format_name" => "Streaming", "record_store_name" => "Napster (" . $this->record_store_webservice_country . ")", "record_store_class_name" => "napster");
                                $response_collapsed["items"][] = array("item" => $item);
                                $item_list[] = $item_name;
                            }
                        }
                    }

                } else if ($this->search_for_type == "album") {
                    /* Convert item_name to collapsed names -  used on artist_page */
                    //$item_name = parent::removeRecordSpecificTextFromItem($item_name);
                    $artist_name = $result[$i]["artist"]["name"];
                    // ID 0 = Main releases
                    // ID 1 = EP
                    if (/*$result[$i]["type"]["id"] == 0 &&*/ strtolower($artist_name) == strtolower($this->search_for_artist)) {
                        /* Convert item_name to collapsed names -  used on artist_page */
                        //$item_name = parent::removeRecordSpecificTextFromItem($item_name);
                        $item_name = $result[$i]["name"];
                        $item_name = cleanItemNameFull($item_name, $m_dbAll->m_dbItemBaseCorrection);
                        
                        /* GET URL */
                        $ch = curl_init();
                        $url = $this->record_store_webservice_url . $result[$i]["id"];
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_HEADER, false);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array());
						curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
                        $results = curl_exec($ch);
                        curl_close($ch);
                        $albums = json_decode($results,true);
                        if (count($albums) == 1) {
                            $buy_at_url = "http://www.napster.com/artist/" . $albums[0]["tokens"]["artist"] . "/album/" . $albums[0]["tokens"]["album"];
                        }
                        /* Make sure that we only add albums, songs that we have in item_base */
                        if ($this->isItemNameInBeginningOfMasterData($item_name, $a_item_names) == true && in_array($item_name, $item_list) == false) {
                            $item = array("artist_name" => $artist_name, "item_name" => $item_name, "buy_at_url" => $buy_at_url, "price_local" => 0, "currency_code" => "DKK", "media_format_name" => "Streaming", "record_store_name" => "Napster (" . $this->record_store_webservice_country . ")", "record_store_class_name" => "napster");
                            $response["items"][] = array("item" => $item);
                            $item_list[] = $item_name;
                            $valid_items[] = $item_name;
                        }
                    }
                } else if ($this->search_for_type == "song") {
                    /* Convert item_name to collapsed names -  used on artist_page */
                    //$item_name = parent::removeRecordSpecificTextFromItem($item_name);
                    /* Make sure that we only add albums, songs where name are in the begining of item name */
                    $item_name = $result[$i]["name"];
                    $artist_name = $result[$i]["artist"]["name"];
                    if (strtolower($item_name) == strtolower($this->search_for_words) && strtolower($artist_name) == strtolower($this->search_for_artist)) {
                        if ($this->isItemNameInBeginningOfMasterData($item_name, $a_item_names) == true && in_array($item_name, $item_list) == false) {
                            $buy_at_url = "http://www.napster.com/artist/" . $artist_name . "/album/" . $result[$i]["album"]["id"] . "/track/" . $result[$i]["id"];
                            $item = array("artist_name" => $artist_name, "item_name" => $item_name, "buy_at_url" => $buy_at_url, "price_local" => 0, "currency_code" => "DKK", "media_format_name" => "Streaming", "record_store_name" => "Napster (" . $this->record_store_webservice_country . ")", "record_store_class_name" => "napster");
                            $response["items"][] = array("item" => $item);
                            $item_list[] = $item_name;
                            $valid_items[] = $item_name;
                        }
                    }
                }
            }
        }
        if (count($valid_items) > 0) {
            if ($this->search_for_type == "artist_album") {
                $valid_items = array_unique($valid_items);
                $response_collapsed["item_count"][] = array("name" => "napster", "type" => "album", "item_count" => count($valid_items));
            } else if ($this->search_for_type == "artist_song") {
                $valid_items = array_unique($valid_items);            
                $response_collapsed["item_count"][] = array("name" => "napster", "type" => "song", "item_count" => count($valid_items));
            } else {
                $response["item_count"][] = array("name" => "napster", "type" => $this->search_for_type, "item_count" => count($valid_items));
            }
        } else {
            if ($this->search_for_type == "artist_album") {
                $response_collapsed["item_count"][] = array("name" => "napster", "type" => "album", "item_count" => 0);
            } else if ($this->search_for_type == "artist_song") {
                $response_collapsed["item_count"][] = array("name" => "napster", "type" => "song", "item_count" => 0);
            } else {
                $response["item_count"][] = array("name" => "napster", "type" => $this->search_for_type, "item_count" => 0);
            }
        }
        
        
        return array("response" => $response, "response_collapsed" => $response_collapsed, "item_count" => count($valid_items));

    }
    
    public function setWebserviceURL()
    {
        if ($this->search_for_type == "artist_album") {
            $this->record_store_webservice_url =  "http://direct.rhapsody.com/metadata/data/methods/getShortcutsByIds.js?developerKey=9I0E5A6D8I1J9A2A&ids=";
        } else if ($this->search_for_type == "artist_song") {
            $this->record_store_webservice_url =  "http://direct.rhapsody.com/metadata/data/methods/getShortcutsByIds.js?developerKey=9I0E5A6D8I1J9A2A&ids=";
        } else if ($this->search_for_type == "album") {
            $this->record_store_webservice_url =  "http://direct.rhapsody.com/metadata/data/methods/getShortcutsByIds.js?developerKey=9I0E5A6D8I1J9A2A&ids=";
        } else if ($this->search_for_type == "song") {
            $this->record_store_webservice_url =  "http://direct.rhapsody.com/metadata/data/methods/getShortcutsByIds.js?developerKey=9I0E5A6D8I1J9A2A&ids=";
        }
    }
    public       $artist_id;
    public       $search_for_words;
    public       $search_for_artist;
    public		 $search_for_type;
    public		 $search_for_type_id;
    public       $item_base_id;
    public       $record_store_id;
    public		 $record_store_webservice_country;
    public       $record_store_webservice_url;
    public       $record_store_affiliate_url;
    public       $record_store_affiliate_encode_times;
    public       $m_dbPDO;
}
?>