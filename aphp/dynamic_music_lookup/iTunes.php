<?php

require_once ("dynamic_music_lookup/BaseDynamicMusic.php");


class iTunes extends BaseDynamicMusic
{
    public function __construct( )
    {
        parent::__construct();
        parent::setSearchForCountryCode();
        parent::setRecordStoreID("iTunes ($this->record_store_webservice_country)");
        parent::setRecordStoreData("iTunes ($this->record_store_webservice_country)");
        parent::setSearchForArtistname();
        parent::setSearchForArtistID();
        parent::setSearchForItemBaseID();
        parent::setSearchForType();
        parent::setSearchForItemname();
    }
	
    public function getDataFromWebservice()
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->record_store_webservice_url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $results = curl_exec($ch);
        curl_close($ch);
        return json_decode($results,true);
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
            foreach ($result['results'] AS $data) {
                if ($this->search_for_type == "artist_album") {
                    if ( $data["collectionType"] == "Album" && strtolower($data['artistName']) == strtolower($this->search_for_artist) && $data["collectionPrice"] > 0 && $data["trackCount"] > 2) {
                        /* Convert item_name to collapsed names -  used on artist_page */
                        $item_name = $data["collectionName"];
                        $item_name = cleanItemNameFull($item_name, $m_dbAll->m_dbItemBaseCorrection);
                        $item_name = parent::removeRecordSpecificTextFromItem($item_name);
                        /* Make sure that we only add albums, songs that we have in item_base */
                        if ($this->isItemNameFoundInMasterData($item_name, $a_item_names) == true && in_array($item_name, $item_list) == false) {
                            $item = array("artist_name" => $data['artistName'], "item_name" => $item_name, "buy_at_url" => $data["collectionViewUrl"] . "&at=10lt9T", "affiliate_url" => $this->record_store_affiliate_url, "affiliate_encode_times" => $this->record_store_affiliate_encode_times, "price_local" => ($data["collectionPrice"]*100), "currency_code" => $data["currency"], "media_format_name" => "MP3", "record_store_name" => "iTunes (" . strtoupper($this->record_store_webservice_country) . ")", "record_store_class_name" => "itunes");
                            $response_collapsed["items"][] = array("item" => $item);
                            $item_list[] = $item_name;
                            $valid_items[] = $item_name;
                        }
                        $all_item = array("artist_name" => $data['artistName'], "item_name" => $item_name, "media_format_name" => "MP3");
                        $response_all_items["items"][] = array("item" => $all_item);
                        $all_items_count++;
                    }
                } else if ($this->search_for_type == "artist_song" && strtolower($data['artistName']) == strtolower($this->search_for_artist) && $data["trackPrice"] > 0) {
                    /* Convert item_name to collapsed names -  used on artist_page */
                    $item_name = $data["trackName"];
                    $item_name = cleanItemNameFull($item_name, $m_dbAll->m_dbItemBaseCorrection);
                    $item_name = parent::removeRecordSpecificTextFromItem($item_name);
                    /* Make sure that we only add albums, songs that we have in item_base */
                    if ($this->isItemNameFoundInMasterData($item_name, $a_item_names) == true && in_array($item_name, $item_list) == false) {
                        $item = array("artist_name" => $data['artistName'], "item_name" => $item_name, "buy_at_url" => $data["trackViewUrl"] . "&at=10lt9T", "affiliate_url" => $this->record_store_affiliate_url, "affiliate_encode_times" => $this->record_store_affiliate_encode_times, "price_local" => ($data["trackPrice"]*100), "currency_code" => $data["currency"], "media_format_name" => "MP3", "record_store_name" => "iTunes (" . strtoupper($this->record_store_webservice_country) . ")", "record_store_class_name" => "itunes");
                        $response_collapsed["items"][] = array("item" => $item);
                        $item_list[] = $item_name;
                        $valid_items[] = $item_name;
                    }
                    $all_item = array("artist_name" => $data['artistName'], "item_name" => $item_name, "media_format_name" => "MP3");
                    $response_all_items["items"][] = array("item" => $all_item);
                    $all_items_count++;
                } else if ($this->search_for_type == "album") {
                    $artist_name = $data['artistName'];
                    $item_name = $data['collectionName'];
                    if ( $data["collectionType"] == "Album" && strtolower($artist_name) == strtolower($this->search_for_artist) && $data["collectionPrice"] > 0) {
                        $item = array("artist_name" => $artist_name, "item_name" => $item_name, "buy_at_url" => $data["collectionViewUrl"] . "&at=10lt9T", "affiliate_url" => $this->record_store_affiliate_url, "affiliate_encode_times" => $this->record_store_affiliate_encode_times, "price_local" => ($data["collectionPrice"]*100), "currency_code" => $data["currency"], "media_format_name" => "MP3", "record_store_name" => "iTunes (" . strtoupper($this->record_store_webservice_country) . ")", "record_store_class_name" => "itunes");
                        $response["items"][] = array("item" => $item);
                        $valid_items[] = $item_name;
                    }
                } else if ($this->search_for_type == "song") {
                    $artist_name = $data['artistName'];
                    $item_name = $data['trackName'];
                    if ( $data["wrapperType"] == "track" && strtolower($artist_name) == strtolower($this->search_for_artist) && $data["trackPrice"] > 0 && in_array($item_name, $item_list) == false) {
                        $item = array("artist_name" => $artist_name, "item_name" => $item_name, "buy_at_url" => $data["trackViewUrl"] . "&at=10lt9T", "affiliate_url" => $this->record_store_affiliate_url, "affiliate_encode_times" => $this->record_store_affiliate_encode_times, "price_local" => ($data["trackPrice"]*100), "currency_code" => $data["currency"], "media_format_name" => "MP3", "record_store_name" => "iTunes (" . strtoupper($this->record_store_webservice_country) . ")", "record_store_class_name" => "itunes");
                        $response["items"][] = array("item" => $item);
                        $item_list[] = $item_name;
                        $valid_items[] = $item_name;
                    }
                }

            }
        }
        
        if (count($valid_items) > 0) {
            if ($this->search_for_type == "artist_album") {
                $valid_items = array_unique($valid_items);
                $response_collapsed["item_count"][] = array("name" => "itunes", "type" => "album", "item_count" => count($valid_items));
                $response_all_items["item_count"][] = array("name" => "itunes", "type" => "album", "item_count" => $all_items_count);
            } else if ($this->search_for_type == "artist_song") {
                $valid_items = array_unique($valid_items);
                $response_collapsed["item_count"][] = array("name" => "itunes", "type" => "song", "item_count" => count($valid_items));
                $response_all_items["item_count"][] = array("name" => "itunes", "type" => "song", "item_count" => $all_items_count);
            } else {
                $response["item_count"][] = array("name" => "itunes", "type" => $this->search_for_type, "item_count" => count($valid_items));
            }
        } else {
            if ($this->search_for_type == "artist_album") {
                $response_all_items["item_count"][] = array("name" => "itunes", "type" => "album", "item_count" => $all_items_count);
            } else if ($this->search_for_type == "artist_song") {
                $response_all_items["item_count"][] = array("name" => "itunes", "type" => "song", "item_count" => $all_items_count);
            }
        }
        
        return array("response" => $response, "response_collapsed" => $response_collapsed, "response_all_items" => $response_all_items, "item_count" => count($valid_items));

    }

    /*
        Itunes used "GB" for "UK" - hacked "$lang".
    */
    
    public function setWebserviceURL()
    {
        if ($this->record_store_webservice_country == "DK") {
            $lang = "DK";
        } else {
            $lang = "GB";
        }
        if ($this->search_for_type == "artist_album") {
            $this->record_store_webservice_url = "http://itunes.apple.com/search?term=" . rawurlencode($this->search_for_artist) . "&entity=album&media=music&limit=500&country=" . $lang;
        } else if ($this->search_for_type == "artist_song") {
            $this->record_store_webservice_url = "http://itunes.apple.com/search?term=" . rawurlencode($this->search_for_artist) . "&entity=song&media=music&limit=500&country=" . $lang;
        } else if ($this->search_for_type == "album") {
            $this->record_store_webservice_url = "http://itunes.apple.com/search?term=" . rawurlencode($this->search_for_words) . "&entity=album&media=music&limit=500&attribute=albumTerm&country=" . $lang;
        } else if ($this->search_for_type == "song") {
            $this->record_store_webservice_url = "http://itunes.apple.com/search?term=" . rawurlencode($this->search_for_words) . "&entity=song&media=music&limit=500&attribute=songTerm&country=" . $lang;
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