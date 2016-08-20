<?php

require_once ("dynamic_music_lookup/BaseDynamicMusic.php");


class WiMP extends BaseDynamicMusic
{

    public function __construct( )
    {
        parent::__construct();
        parent::setRecordStoreID("WiMP (DK)");
        parent::setRecordStoreData("WiMP (DK)");
        parent::setSearchForArtistname();
        parent::setSearchForArtistID();
        parent::setSearchForItemBaseID();
        parent::setSearchForType();
        parent::setSearchForItemname();
        parent::setSearchForCountryCode();
                        
    }
	
    public function getDataFromWebservice()
    {
        $artist_id = 0;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://stage.wimpmusic.com/partnerapi/artist/search/" . rawurlencode($this->search_for_artist));
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json','PartnerKey: 8fb0d8ce-4e36-49bd-b540-a689ccc'));
        $results = curl_exec($ch);
        curl_close($ch);
        $artists = json_decode($results,true);
        
        if (count($artists) > 0) {
            for ( $i=0; $i <= count($artists); $i++) {
                if (strtolower($artists[$i]["artist"]["name"]) == strtolower($this->search_for_artist)) {
                    $artist_id = $artists[$i]["artist"]["id"];
                }
            }
        }

        if ($artist_id != 0) {
            if ($this->search_for_type == "song" || $this->search_for_type == "artist_song") {
                $maxtracks = "?maxtracks=500";
            } else { $maxtracks = ""; }
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->record_store_webservice_url . $artist_id . $maxtracks);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json','PartnerKey: 8fb0d8ce-4e36-49bd-b540-a689ccc'));
            $results = curl_exec($ch);
            curl_close($ch);
        }

        return json_decode($results,true);
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
                    $artist_name = $result[$i]["album"]["artist"]["name"];
                    
                    if ( $result[$i]["album"]["type"] == "Album" && strtolower($artist_name) == strtolower($this->search_for_artist)) {
                        /* Convert item_name to collapsed names -  used on artist_page */
                        //$item_name = parent::removeRecordSpecificTextFromItem($item_name);
                        $item_name = $result[$i]["album"]["title"];
                        $valid_items[] = $item_name;
                        $item_name = cleanItemNameFull($item_name, $m_dbAll->m_dbItemBaseCorrection);
                        /* Make sure that we only add albums, songs that we have in item_base */
                        if ($this->isItemNameFoundInMasterData($item_name, $a_item_names) == true && in_array($item_name, $item_list) == false) {
                            $item = array("artist_name" => $artist_name, "item_name" => $item_name, "buy_at_url" => "http://wimp.dk/album/" . $result[$i]["album"]["id"], "price_local" => 0, "currency_code" => "DKK", "media_format_name" => "Streaming", "record_store_name" => "WiMP (" . $this->record_store_webservice_country . ")", "record_store_class_name" => "wimp");
                            $response_collapsed["items"][] = array("item" => $item);
                            $item_list[] = $item_name;
                        }
                    }
                } else if ($this->search_for_type == "artist_song") {
                    $artist_name = $result[$i]["track"]["artist"]["name"];
                    //if ( $result[$i]["track"]["album"]["type"] == "Album" && strtolower($artist_name) == strtolower($this->search_for_artist)) {
                    if (strtolower($artist_name) == strtolower($this->search_for_artist)) {
                        /* Convert item_name to collapsed names -  used on artist_page */
                        //$item_name = parent::removeRecordSpecificTextFromItem($item_name);
                        $item_name = $result[$i]["track"]["title"];
                        $valid_items[] = $item_name;
                        $item_name = cleanItemNameFull($item_name, $m_dbAll->m_dbItemBaseCorrection);
                        /* Make sure that we only add albums, songs that we have in item_base */
                        if ($this->isItemNameFoundInMasterData($item_name, $a_item_names) == true && in_array($item_name, $item_list) == false) {
                            $item = array("artist_name" => $artist_name, "item_name" => $item_name, "buy_at_url" => $result[$i]["track"]["url"], "price_local" => 0, "currency_code" => "DKK", "media_format_name" => "Streaming", "record_store_name" => "WiMP (" . $this->record_store_webservice_country . ")", "record_store_class_name" => "wimp");
                            $response_collapsed["items"][] = array("item" => $item);
                            $item_list[] = $item_name;
                        }
                    }
                } else if ($this->search_for_type == "album") {
                    $artist_name = $result[$i]["album"]["artist"]["name"];
                    $item_name = $result[$i]["album"]["title"];
                    if ( $result[$i]["album"]["type"] == "Album" && strtolower($artist_name) == strtolower($this->search_for_artist)) {
                        if ($this->isItemNameInBeginningOfMasterData($item_name, $a_item_names) == true) {
                            $item = array("artist_name" => $artist_name, "item_name" => $item_name, "buy_at_url" => "http://wimp.dk/album/" . $result[$i]["album"]["id"], "price_local" => 0, "currency_code" => "DKK", "media_format_name" => "Streaming", "record_store_name" => "WiMP (" . $this->record_store_webservice_country . ")", "record_store_class_name" => "wimp");
                            $response["items"][] = array("item" => $item);
                            $valid_items[] = $item_name;
                        }
                    }
                } else if ($this->search_for_type == "song") {
                    /* Make sure that we only add albums, songs where name are in the begining of item name */
                    $item_name = $result[$i]["track"]["title"];
                    $artist_name = $result[$i]["track"]["artist"]["name"];
                    if ($result[$i]["track"]["album"]["type"] == "Album" && strtolower($artist_name) == strtolower($this->search_for_artist)) {
                        if ($this->isItemNameInBeginningOfMasterData($item_name, $a_item_names) == true && in_array($item_name, $item_list) == false) {
                            $item = array("artist_name" => $artist_name, "item_name" => $item_name, "buy_at_url" => $result[$i]["track"]["url"], "price_local" => 0, "currency_code" => "DKK", "media_format_name" => "Streaming", "record_store_name" => "WiMP (" . $this->record_store_webservice_country . ")", "record_store_class_name" => "wimp");
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
                $response_collapsed["item_count"][] = array("name" => "wimp", "type" => "album", "item_count" => count($valid_items));
            } else if ($this->search_for_type == "artist_song") {
                $valid_items = array_unique($valid_items);            
                $response_collapsed["item_count"][] = array("name" => "wimp", "type" => "song", "item_count" => count($valid_items));
            } else {
                $response["item_count"][] = array("name" => "wimp", "type" => $this->search_for_type, "item_count" => count($valid_items));
            }
        } else {
            if ($this->search_for_type == "artist_album") {
                $response_collapsed["item_count"][] = array("name" => "wimp", "type" => "album", "item_count" => 0);
            } else if ($this->search_for_type == "artist_song") {
                $response_collapsed["item_count"][] = array("name" => "wimp", "type" => "song", "item_count" => 0);
            } else {
                $response["item_count"][] = array("name" => "wimp", "type" => $this->search_for_type, "item_count" => 0);
            }
        }
        
        
        return array("response" => $response, "response_collapsed" => $response_collapsed, "item_count" => count($valid_items));

    }
    
    public function setWebserviceURL()
    {
        if ($this->search_for_type == "artist_album") {
            $this->record_store_webservice_url =  "http://stage.wimpmusic.com/partnerapi/album/artistid/";
        } else if ($this->search_for_type == "artist_song") {
            $this->record_store_webservice_url =  "http://stage.wimpmusic.com/partnerapi/track/artistid/";
        } else if ($this->search_for_type == "album") {
            $this->record_store_webservice_url =  "http://stage.wimpmusic.com/partnerapi/album/artistid/";
        } else if ($this->search_for_type == "song") {
            $this->record_store_webservice_url =  "http://stage.wimpmusic.com/partnerapi/track/artistid/";
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