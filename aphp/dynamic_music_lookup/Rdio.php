<?php

require_once ("dynamic_music_lookup/BaseDynamicMusic.php");
require_once ("dynamic_music_lookup/RdioAPI.class.php");

class Rdio extends BaseDynamicMusic
{

    public function __construct( )
    {
        parent::__construct();
        parent::setRecordStoreID("Rdio (UK)");
        parent::setRecordStoreData("Rdio (UK)");
        parent::setSearchForArtistname();
        parent::setSearchForArtistID();
        parent::setSearchForItemBaseID();
        parent::setSearchForType();
        parent::setSearchForItemname();
        parent::setSearchForCountryCode();
    }
	
    public function getDataFromWebservice()
    {
        $artist_key = "";
        $results = array();
        define('RDIO_CONSUMER_KEY', 'vsgehkqdymhwpft43z892ud7');
        define('RDIO_CONSUMER_SECRET', '2McBX7NjCH');
   
        # create an instance of the Rdio object with our consumer credentials
        $rdio = new RdioAPI(array(RDIO_CONSUMER_KEY, RDIO_CONSUMER_SECRET));

        // Get Rdio unique key for an artist 
        $lookup_artist = $rdio->call("search", array("query" => $this->search_for_artist, "types" => "Artist")); 
        
        $search_results_artist = $lookup_artist->result->results;

        if (count($search_results_artist) > 0) {
            $artist_key = $rdio->getArtistKeyKeyFromName($search_results_artist, $this->search_for_artist);
            if ($artist_key == "r377297") { // "Tina Dickow / Tina Dico" testing.
                $artist_key = "r35540";
            }
        }

        if ($artist_key != "") {
            if ($this->record_store_webservice_country == "DK") {
                $lang = "DK";
            } else {
                $lang = "GB";
            }
            if ($this->search_for_type == "album" || $this->search_for_type == "artist_album") {
                $result = $rdio->call("getAlbumsForArtist", array("artist" => $artist_key, "appears_on" => "false", "count" => "500", "_region" => $lang));
            } else if ($this->search_for_type == "song" || $this->search_for_type == "artist_song") {
                $result = $rdio->call("getTracksForArtist", array("artist" => $artist_key, "appears_on" => "false", "count" => "500", "_region" => $lang)); 
            }
        }
        $result = (array)$result;

        for ( $i=0; $i < count($result["result"]); $i++) {
            $item = (array)$result["result"][$i];
            if ($this->search_for_type == "artist_album" || $this->search_for_type == "album") {
                $results[] = array("item_name" => $item["name"], "artist_name" => $item["artist"], "buy_at_url" => "http://www.rdio.com" . $item["url"], "price_local" => 0);
            } else if ($this->search_for_type == "artist_song" || $this->search_for_type == "song") {
                $results[] = array("item_name" => $item["name"], "artist_name" => $item["artist"], "buy_at_url" => "http://www.rdio.com" . $item["url"], "price_local" => 0);
            }
        }

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
                    /* Convert item_name to collapsed names -  used on artist_page */
                    $item_name = $result[$i]["item_name"];
                    $valid_items[] = $item_name;
                    //$item_name = parent::removeRecordSpecificTextFromItem($item_name);
                    $item_name = cleanItemNameFull($item_name, $m_dbAll->m_dbItemBaseCorrection );
                    /* Make sure that we only add albums, songs that we have in item_base */
                    if ($this->isItemNameFoundInMasterData($item_name, $a_item_names) == true && in_array($item_name, $item_list) == false) {
                        $item = array("artist_name" => $result[$i]['artist_name'], "item_name" => $item_name, "buy_at_url" => $result[$i]["buy_at_url"], "affiliate_url" => $this->record_store_affiliate_url, "affiliate_encode_times" => $this->record_store_affiliate_encode_times, "price_local" => 0, "currency_code" => "DKK", "media_format_name" => "Streaming", "record_store_name" => "Rdio", "record_store_class_name" => "rdio");
                        $response_collapsed["items"][] = array("item" => $item);
                        $item_list[] = $item_name;
                    }
                } else if ($this->search_for_type == "artist_song") {
                    /* Convert item_name to collapsed names -  used on artist_page */
                    $item_name = $result[$i]["item_name"];
                    $valid_items[] = $item_name;
                    //$item_name = parent::removeRecordSpecificTextFromItem($item_name);
                    $item_name = cleanItemNameFull($item_name, $m_dbAll->m_dbItemBaseCorrection);
                    /* Make sure that we only add albums, songs that we have in item_base */
                    if ($this->isItemNameFoundInMasterData($item_name, $a_item_names) == true && in_array($item_name, $item_list) == false) {
                        $item = array("artist_name" => $result[$i]['artist_name'], "item_name" => $item_name, "buy_at_url" => $result[$i]["buy_at_url"], "affiliate_url" => $this->record_store_affiliate_url, "affiliate_encode_times" => $this->record_store_affiliate_encode_times, "price_local" => 0, "currency_code" => "DKK", "media_format_name" => "Streaming", "record_store_name" => "Rdio", "record_store_class_name" => "rdio");
                        $response_collapsed["items"][] = array("item" => $item);
                        $item_list[] = $item_name;
                    }
                } else if ($this->search_for_type == "album") {
                    /* Convert item_name to collapsed names -  used on artist_page */
                    $item_name = $result[$i]["item_name"];
                    /* Make sure that we only add albums, songs that we have in item_base */
                    if ($this->isItemNameInBeginningOfMasterData($item_name, $a_item_names) == true) {
                        $item = array("artist_name" => $result[$i]['artist_name'], "item_name" => $item_name, "buy_at_url" => $result[$i]["buy_at_url"], "affiliate_url" => $this->record_store_affiliate_url, "affiliate_encode_times" => $this->record_store_affiliate_encode_times, "price_local" => 0, "currency_code" => "DKK", "media_format_name" => "Streaming", "record_store_name" => "Rdio", "record_store_class_name" => "rdio");
                        $response["items"][] = array("item" => $item);
                        $valid_items[] = $item_name;
                        $item_list[] = $item_name;
                    }
                } else if ($this->search_for_type == "song") {
                    /* Convert item_name to collapsed names -  used on artist_page */
                    $item_name = $result[$i]["item_name"];
                    /* Make sure that we only add albums, songs that we have in item_base */
                    if ($this->isItemNameInBeginningOfMasterData($item_name, $a_item_names) == true && in_array($item_name, $item_list) == false) {
                        $item = array("artist_name" => $result[$i]['artist_name'], "item_name" => $item_name, "buy_at_url" => $result[$i]["buy_at_url"], "affiliate_url" => $this->record_store_affiliate_url, "affiliate_encode_times" => $this->record_store_affiliate_encode_times, "price_local" => 0, "currency_code" => "DKK", "media_format_name" => "Streaming", "record_store_name" => "Rdio", "record_store_class_name" => "rdio");
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
                $response_collapsed["item_count"][] = array("name" => "rdio", "type" => "album", "item_count" => count($valid_items));
            } else if ($this->search_for_type == "artist_song") {
                $valid_items = array_unique($valid_items);            
                $response_collapsed["item_count"][] = array("name" => "rdio", "type" => "song", "item_count" => count($valid_items));
            } else {
                $response["item_count"][] = array("name" => "rdio", "type" => $this->search_for_type, "item_count" => count($valid_items));
            }
        } else {
            if ($this->search_for_type == "artist_album") {
                $response_collapsed["item_count"][] = array("name" => "rdio", "type" => "album", "item_count" => 0);
            } else if ($this->search_for_type == "artist_song") {
                $response_collapsed["item_count"][] = array("name" => "rdio", "type" => "song", "item_count" => 0);
            } else {
                $response["item_count"][] = array("name" => "rdio", "type" => $this->search_for_type, "item_count" => 0);
            }
        }
        
        return array("response" => $response, "response_collapsed" => $response_collapsed, "item_count" => count($valid_items));
    }
    
    public function setWebserviceURL()
    {
        if ($this->search_for_type == "artist_album") {
            $this->record_store_webservice_url =  "http://stage.Rdiomusic.com/partnerapi/album/search/" . rawurlencode($this->search_for_artist) . "%20" . rawurlencode($this->search_for_words);
        } else if ($this->search_for_type == "artist_song") {
            $this->record_store_webservice_url =  "http://stage.Rdiomusic.com/partnerapi/track/search/" . rawurlencode($this->search_for_artist) . "%20" . rawurlencode($this->search_for_words);
        } else if ($this->search_for_type == "album") {
            $this->record_store_webservice_url =  "http://stage.Rdiomusic.com/partnerapi/album/search/" . rawurlencode($this->search_for_artist) . "%20" . rawurlencode($this->search_for_words);
        } else if ($this->search_for_type == "song") {
            $this->record_store_webservice_url =  "http://stage.Rdiomusic.com/partnerapi/track/search/" . rawurlencode($this->search_for_artist) . "%20" . rawurlencode($this->search_for_words);
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