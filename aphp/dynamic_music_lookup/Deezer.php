<?php

require_once ("dynamic_music_lookup/BaseDynamicMusic.php");


class Deezer extends BaseDynamicMusic
{

    public function __construct( )
    {
        parent::__construct();
        parent::setRecordStoreID("Deezer (UK)");
        parent::setRecordStoreData("Deezer (UK)");
        parent::setSearchForArtistname();
        parent::setSearchForArtistID();
        parent::setSearchForItemBaseID();
        parent::setSearchForType();
        parent::setSearchForItemname();
        parent::setSearchForCountryCode();
    }
	

    public function getDataFromWebservice()
    {

        $results = array();
        $artist_id = 0;
        

        if ($this->search_for_type == "artist_album" || $this->search_for_type == "album") {
        
            // Url Encode for at man kan søge på "Johnny Madsen" - dette skal være et UTF-8 format.
            // Bug virker ikke med Lis Sørensen.
            $artist_url = "http://api.deezer.com/2.0/search/artist?q=" . urlencode($this->search_for_artist);

            $file_content = file_get_contents( $artist_url );
            $result = json_decode($file_content, true);

            /* Get Artist id */
            if (count($result["data"]) > 0) {
                for ($i=0;$i <= count($result["data"]); $i++) {
                    if (strtolower($result["data"][$i]["name"]) == strtolower($this->search_for_artist)) {
                        $artist_id = $result["data"][$i]["id"];
                        break;
                    };
                }
            }
            
            if ($artist_id != 0) {
                $file_contents = file_get_contents( $this->record_store_webservice_url . "artist/" . $artist_id . "/albums" );
                $result = json_decode($file_contents, true);
                $results = $result["data"];
                // make sure only have valid artist in the array
                //for ($l = 1; $l <= count($result["data"]); $l++) {
                    //if (strtolower($result["data"][$l]["artist"]["name"]) == strtolower($this->search_for_artist)) {
                        //$results = $result["data"][$l];
                    //}
                //}

                /*if ($result["total"] > 50) {
                    for ($i = 1; $i < floor($result["total"]/50); $i++) {
                        $offset = "&index=" . (50*$i);
                        $url = substr($result["next"], 0, strpos($result["next"], "&index="));
                        $file_contents = file_get_contents( $url . $offset );
                        $page_result = json_decode($file_contents, true);
                        // make sure only have valid artist in the array
                        for ($j = 1; $j < count($page_result["data"]); $j++) {
                            if (strtolower($page_result["data"][$j]["artist"]["name"]) == strtolower($this->search_for_artist)) {
                                $results[] = $page_result["data"][$j];
                            }
                        }
                    }
                }*/
            }
        } else if ($this->search_for_type == "artist_song" || $this->search_for_type == "song") {
            $results = array();
            $file_contents = file_get_contents( $this->record_store_webservice_url );
            $result = json_decode($file_contents, true);


            for ($i = 0; $i <= count($result["data"]); $i++) {
                if (strtolower($result["data"][$i]["artist"]["name"]) == strtolower($this->search_for_artist)) {
                    $results[] = $result["data"][$i];
                }
            }
            
            for ($i = 1; $i <= floor($result["total"]/50); $i++) {
                $offset = "&index=" . (50*$i);
                $file_contents = file_get_contents( $this->record_store_webservice_url . $offset );
                $result = json_decode($file_contents, true);
                if (strtolower($result["data"][$i]["artist"]["name"]) == strtolower($this->search_for_artist)) {
                    $results[] = $result["data"][$i];
                }
                
            }
        }
    
        /*$results = array();
        $file_contents = file_get_contents( $this->record_store_webservice_url );
        $result = json_decode($file_contents, true);

        $results = array_merge((array)$results, (array)$result["data"]);
        
        for ($i = 1; $i < floor($result["total"]/50); $i++) {
            $offset = "&index=" . (50*$i);
            $url = substr($result["next"], 0, strpos($result["next"], "&index="));
            $file_contents = file_get_contents( $url . $offset );
            $result = json_decode($file_contents, true);
            $results = array_merge((array)$results, (array)$result["data"]);
        }        return $results;*/


        return $results;
    }
	
    public function parseDataFromWebservice($result, &$a_item_names)
    {
        $response = array();
        $response_collapsed = array();
        $item_list = array();   // Array used to limit number of album/song - to only list same album/song once.
        $valid_items = array(); // Array used to names of valid items - like counting number of albums/songs for an artist.
        
        if (count($result) > 0) {
            for ( $i=0; $i <= count($result); $i++) {
                if ($this->search_for_type == "artist_album") {
                       //$item_name = parent::removeRecordSpecificTextFromItem($result[$i]["title"]);
                        $item_name = $result[$i]["title"];
                        $valid_items[] = $item_name; // Count non-collapsed albums like "Delta Machine" and "Delta Mschine (Deluxe)".
                        $item_name = cleanItemNameFull($item_name, $m_dbAll->m_dbItemBaseCorrection );
                        if ($this->isItemNameFoundInMasterData($item_name, $a_item_names) == true && in_array($item_name, $item_list) == false) {
                            $item = array("artist_name" => $this->search_for_artist, "item_name" => $item_name, "buy_at_url" => "http://www.deezer.com/album/" . $result[$i]["id"] . "?app_id=116135", "price_local" => 0, "currency_code" => "DKK", "media_format_name" => "Streaming", "record_store_name" => "Deezer (" . $this->record_store_webservice_country . ")", "record_store_class_name" => "deezer");
                            $response_collapsed["items"][] = array("item" => $item);
                            $item_list[] = $item_name;
                        }
                    /*if (strtolower($result[$i]["artist"]["name"]) == strtolower($this->search_for_artist)) {
                       //$item_name = parent::removeRecordSpecificTextFromItem($result[$i]["title"]);
                        $item_name = $result[$i]["album"]["title"];
                        $valid_items[] = $item_name; // Count non-collapsed albums like "Delta Machine" and "Delta Mschine (Deluxe)".
                        $item_name = cleanItemNameFull($item_name, $m_dbAll->m_dbItemBaseCorrection );
                        if ($this->isItemNameFoundInMasterData($item_name, $a_item_names) == true && in_array($item_name, $item_list) == false) {
                            $item = array("artist_name" => $result[$i]["artist"]["name"], "item_name" => $item_name, "buy_at_url" => "http://www.deezer.com/album/" . $result[$i]["album"]["id"] . "?app_id=116135", "price_local" => 0, "currency_code" => "DKK", "media_format_name" => "Streaming", "record_store_name" => "Deezer (" . $this->record_store_webservice_country . ")", "record_store_class_name" => "deezer");
                            $response_collapsed["items"][] = array("item" => $item);
                            $item_list[] = $item_name;
                        }
                    }*/
                } else if ($this->search_for_type == "artist_song") {
                    if (strtolower($result[$i]["artist"]["name"]) == strtolower($this->search_for_artist)) {
                        //$item_name = parent::removeRecordSpecificTextFromItem($result[$i]["title"]);
                        $item_name = $result[$i]["title"];
                        $valid_items[] = $item_name;  // Count non-collapsed albums like "Delta Machine" and "Delta Mschine (Deluxe)".
                        $item_name = cleanItemNameFull($item_name, $m_dbAll->m_dbItemBaseCorrection );
                        if ($this->isItemNameFoundInMasterData($item_name, $a_item_names) == true && in_array($item_name, $item_list) == false) {
                            $item = array("artist_name" => $result[$i]["artist"]["name"], "item_name" => $item_name, "buy_at_url" => $result[$i]["link"] . "?app_id=116135", "price_local" => 0, "currency_code" => "DKK", "media_format_name" => "Streaming", "record_store_name" => "Deezer (" . $this->record_store_webservice_country . ")", "record_store_class_name" => "deezer");
                            $response_collapsed["items"][] = array("item" => $item);
                            $item_list[] = $item_name;
                        }
                    }
                } else if ($this->search_for_type == "album") {
                        //$item_name = parent::removeRecordSpecificTextFromItem($result[$i]["title"]);
                        $item_name = $result[$i]["title"];
                        $item_name = cleanItemNameFull($item_name, $m_dbAll->m_dbItemBaseCorrection );
                        if ($this->isItemNameFoundInMasterData($item_name, $a_item_names) == true && in_array($item_name, $item_list) == false) {
                            $item = array("artist_name" => $this->search_for_artist, "item_name" => $item_name, "buy_at_url" => "http://www.deezer.com/album/" . $result[$i]["id"] . "?app_id=116135", "price_local" => 0, "currency_code" => "DKK", "media_format_name" => "Streaming", "record_store_name" => "Deezer (" . $this->record_store_webservice_country . ")", "record_store_class_name" => "deezer");
                            $response["items"][] = array("item" => $item);
                            $item_list[] = $item_name;
                            $valid_items[] = $item_name;
                        }
                    /*if (strtolower($result[$i]["artist"]["name"]) == strtolower($this->search_for_artist)) {
                        //$item_name = parent::removeRecordSpecificTextFromItem($result[$i]["title"]);
                        $item_name = $result[$i]["album"]["title"];
                        $buy_at_url = "http://www.deezer.com/album/" . $result[$i]["album"]["id"];
                        if ($this->isItemNameInBeginningOfMasterData($item_name, $a_item_names) == true && in_array($buy_at_url, $item_list) == false) {
                            $item = array("artist_name" => $result[$i]["artist"]["name"], "item_name" => $item_name, "buy_at_url" => "http://www.deezer.com/album/" . $result[$i]["album"]["id"] . "?app_id=116135", "price_local" => 0, "currency_code" => "DKK", "media_format_name" => "Streaming", "record_store_name" => "Deezer (" . $this->record_store_webservice_country . ")", "record_store_class_name" => "deezer");
                            $response["items"][] = array("item" => $item);
                            $item_list[] = $buy_at_url;
                            $valid_items[] = $item_name;
                        }
                    }*/
                } else if ($this->search_for_type == "song") {
                    if (strtolower($result[$i]["artist"]["name"]) == strtolower($this->search_for_artist)) {
                        //$item_name = parent::removeRecordSpecificTextFromItem($result[$i]["title"]);
                        $item_name = $result[$i]["title"];
                        if ($this->isItemNameInBeginningOfMasterData($item_name, $a_item_names) == true && in_array($item_name, $item_list) == false) {
                            $valid_items[] = $item_name;
                            if (in_array($item_name, $item_list) == false) {
                                $item = array("artist_name" => $result[$i]["artist"]["name"], "item_name" => $item_name, "buy_at_url" => $result[$i]["link"] . "?app_id=116135", "price_local" => 0, "currency_code" => "DKK", "media_format_name" => "Streaming", "record_store_name" => "Deezer (" . $this->record_store_webservice_country . ")", "record_store_class_name" => "deezer");
                                $response["items"][] = array("item" => $item);
                                $item_list[] = $item_name;
                            }
                        }
                    }
                }
            }
        }
        
        if (count($valid_items) > 0) {
            if ($this->search_for_type == "artist_album") {
                $valid_items = array_unique($valid_items);
                $response_collapsed["item_count"][] = array("name" => "deezer", "type" => "album", "item_count" => count($valid_items));
            } else if ($this->search_for_type == "artist_song") {
                $valid_items = array_unique($valid_items);
                $response_collapsed["item_count"][] = array("name" => "deezer", "type" => "song", "item_count" => count($valid_items));
            } else {
                $response["item_count"][] = array("name" => "deezer", "type" => $this->search_for_type, "item_count" => count($valid_items));
            }
        } else {
            if ($this->search_for_type == "artist_album") {
                $response_collapsed["item_count"][] = array("name" => "deezer", "type" => "album", "item_count" => 0);
            } else if ($this->search_for_type == "artist_song") {
                $response_collapsed["item_count"][] = array("name" => "deezer", "type" => "song", "item_count" => 0);
            } else {
                $response["item_count"][] = array("name" => "deezer", "type" => $this->search_for_type, "item_count" => 0);
            }
        }

        return array("response" => $response, "response_collapsed" => $response_collapsed, "item_count" => count($valid_items));

    }
    
    public function setWebserviceURL()
    {
        if ($this->search_for_type == "artist_album") {
            $this->record_store_webservice_url = "http://api.deezer.com/2.0/";
        } else if ($this->search_for_type == "artist_song") {
            $this->record_store_webservice_url = "http://api.deezer.com/2.0/search?output=json&order=RANKING&q=" . urlencode($this->search_for_artist);
        } else if ($this->search_for_type == "album") {
            $this->record_store_webservice_url = "http://api.deezer.com/2.0/";
        } else if ($this->search_for_type == "song") {
            $this->record_store_webservice_url = "http://api.deezer.com/2.0/search?output=json&order=RANKING&q=" . urlencode($this->search_for_words);
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