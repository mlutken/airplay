<?php

require_once ("dynamic_music_lookup/BaseDynamicMusic.php");


class Spotify extends BaseDynamicMusic
{

    public function __construct( )
    {
        parent::__construct();
        parent::setSearchForCountryCode();
        parent::setRecordStoreID("Spotify ($this->record_store_webservice_country)");
        parent::setRecordStoreData("Spotify ($this->record_store_webservice_country)");
        parent::setSearchForArtistname();
        parent::setSearchForArtistID();
        parent::setSearchForItemBaseID();
        parent::setSearchForType();
        parent::setSearchForItemname();
    }
	
    public function parseDataFromWebservice($result, &$a_item_names)
    {
//print "<pre>";
//	var_dump($result);
        $response = array();
        $response_collapsed = array();
        $item_list = array();   // Array used to limit number of album/song - to only list same album/song once.
        $valid_items = array(); // Array used to names of valid items - like counting number of albums/songs for an artist.

        if (count($result) > 0) {
            foreach ($result['results'] AS $data) {
                if ($this->search_for_type == "artist_album") {
                    if (strtolower($data['artist_name']) == strtolower($this->search_for_artist)) {
                        /* Convert item_name to collapsed names -  used on artist_page */
                        //$item_name = $this->removeRecordSpecificTextFromItem($data["item_name"]);
                        $item_name = $data["item_name"];
                        $valid_items[] = $item_name; // Count non-collapsed albums like "Delta Machine" and "Delta Mschine (Deluxe)".
                        $item_name = cleanItemNameFull($item_name, $m_dbAll->m_dbItemBaseCorrection);
                        /* Make sure that we only add albums, songs that we have in item_base */
                        if ($this->isItemNameFoundInMasterData($item_name, $a_item_names) == true && in_array($item_name, $item_list) == false) {
                            $item = array("artist_name" => $data['artist_name'], "item_name" => $item_name, "buy_at_url" => str_replace('spotify:album:', 'https://play.spotify.com/album/', $data["buy_at_url"]), "price_local" => 0, "currency_code" => "DKK", "media_format_name" => "Streaming", "record_store_name" => "Spotify (" . $this->record_store_webservice_country . ")", "record_store_class_name" => "spotify");
                            $response_collapsed["items"][] = array("item" => $item);
                            $item_list[] = $item_name;
                        }
                    }
                 } else if ($this->search_for_type == "artist_song") {
                    if (strtolower($data['artist_name']) == strtolower($this->search_for_artist)) {
                        // Convert item_name to collapsed names -  used on artist_page
                        //$item_name = $this->removeRecordSpecificTextFromItem($data["item_name"]);
                        $item_name = $data["item_name"];
                        $valid_items[] = $item_name; // Count non-collapsed albums like "Delta Machine" and "Delta Mschine (Deluxe)".
                        $item_name = cleanItemNameFull($item_name, $m_dbAll->m_dbItemBaseCorrection);
                        // Make sure that we only add albums, songs that we have in item_base 
                        if ($this->isItemNameFoundInMasterData($item_name, $a_item_names) == true && in_array($item_name, $item_list) == false) {
                            $item = array("artist_name" => $data['artist_name'], "item_name" => $item_name, "buy_at_url" => str_replace('spotify:track:', 'https://play.spotify.com/track/', $data["buy_at_url"]), "price_local" => 0, "currency_code" => "DKK", "media_format_name" => "Streaming", "record_store_name" => "Spotify (" . $this->record_store_webservice_country . ")", "record_store_class_name" => "spotify");
                            $response_collapsed["items"][] = array("item" => $item);
                            $item_list[] = $item_name;
                        }
                    }
                } else if ($this->search_for_type == "album") {
                    if (strtolower($data['artist_name']) == strtolower($this->search_for_artist)) {
                        /* Convert item_name to collapsed names -  used on artist_page */
                        //$item_name = $this->removeRecordSpecificTextFromItem($data["item_name"]);
                        $item_name = $data["item_name"];
                        /* Make sure that we only add albums, songs where name are in the begining of item name */
                        if ($this->isItemNameInBeginningOfMasterData($item_name, $a_item_names) == true) {
                            $item = array("artist_name" => $data['artist_name'], "item_name" => $item_name, "buy_at_url" => str_replace('spotify:album:', 'https://play.spotify.com/album/', $data["buy_at_url"]), "price_local" => 0, "currency_code" => "DKK", "media_format_name" => "Streaming", "record_store_name" => "Spotify (" . $this->record_store_webservice_country . ")", "record_store_class_name" => "spotify");
                            $response["items"][] = array("item" => $item);
                            $valid_items[] = $item_name;
                        }
                    }
               
                } else if ($this->search_for_type == "song") {
                    if (strtolower($data['artist_name']) == strtolower($this->search_for_artist)) {
                        /* Convert item_name to collapsed names -  used on artist_page */
                        //$item_name = $this->removeRecordSpecificTextFromItem($data["item_name"]);
                        $item_name = $data["item_name"];
                        /* Make sure that we only add albums, songs where name are in the begining of item name */
                        if ($this->isItemNameInBeginningOfMasterData($item_name, $a_item_names) == true && in_array($item_name, $item_list) == false) {
                            $item = array("artist_name" => $data['artist_name'], "item_name" => $item_name, "buy_at_url" => str_replace('spotify:track:', 'https://play.spotify.com/track/', $data["buy_at_url"]), "price_local" => 0, "currency_code" => "DKK", "media_format_name" => "Streaming", "record_store_name" => "Spotify (" . $this->record_store_webservice_country . ")", "record_store_class_name" => "spotify");
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
                $response_collapsed["item_count"][] = array("name" => "spotify", "type" => "album", "item_count" => count($valid_items));
            } else if ($this->search_for_type == "artist_song") {
                $response_collapsed["item_count"][] = array("name" => "spotify", "type" => "song", "item_count" => count($valid_items));
                $valid_items = array_unique($valid_items);
            } else {
                $response["item_count"][] = array("name" => "spotify", "type" => $this->search_for_type, "item_count" => count($valid_items));
            }
        } else {
            if ($this->search_for_type == "artist_album") {
                $response_collapsed["item_count"][] = array("name" => "spotify", "type" => "album", "item_count" => 0);
            } else if ($this->search_for_type == "artist_song") {
                $response_collapsed["item_count"][] = array("name" => "spotify", "type" => "song", "item_count" => 0);
            } else {
                $response["item_count"][] = array("name" => "spotify", "type" => $this->search_for_type, "item_count" => 0);
            }
        }
        
        return array("response" => $response, "response_collapsed" => $response_collapsed, "item_count" => count($valid_items));

    }
    
    public function getDataFromWebservice()
    {
        if ($this->search_for_type == "artist_album") {
            $item = "album";
        } else if ($this->search_for_type == "artist_song") {
            $item = "track";
        } else if ($this->search_for_type == "album") {
            $item = "album";
        } else if ($this->search_for_type == "song") {
            $item = "track";
        }
        // || $this->search_for_type == "artist_song" - VIRKER IKKE
        if ($this->search_for_type == "artist_album" || $this->search_for_type == "album") {
            /* Get All artists with a given search string */
            $artists = $this->searchArtist($this->search_for_artist);
            /* Get names for getting unique artist */
            $artist_names = $this->getArtistNames($artists);
			//print "<pre>";
			//var_dump($artist_names);
            /* Loop to make sure that we have the correct artist name - if we search "Depeche Mode", then we filter of "Depeche Mode - Tribute" */
            for ($i = 0; $i <= count($artist_names); $i++) {
                if (strtolower($artist_names[$i]["name"]) == strtolower($this->search_for_artist)) {
                    $id = $artist_names[$i]["id"];
                }
            }
			// Wrong Kashmir - make more smart ...
			if ($id == "spotify:artist:2M6KSMQtb9N2xWrpsvFm7t") {
				$id = "spotify:artist:6Jsq0AbwEKcmDuyA4ca9wu";
			} 
			// Wrong Nephew
			if ($id == "spotify:artist:3rIPPywm3KF56WjcVqDRVG") {
				$id = "spotify:artist:11BUDylkl50Y6dsbZMZiCG";
			} 
			// Wrong Nirvana
			/*if ($id = "spotify:artist:6olE6TJLqED3rqDCT0FyPh") {
				$id = "spotify:artist:7dIxU1XgxBIa3KJAWzaFAC";
			}*/
            /* Get all albums/songs for a given artist */
            $items = $this->lookup($id, $item);
        } else if ($this->search_for_type == "song") {
            $items = $this->searchTrack($this->search_for_words);
        } else if ($this->search_for_type == "artist_song") {

            // Get All artists with a given search string
            $artists = $this->searchArtist($this->search_for_artist);
            // Get names for getting unique artist
            $artist_names = $this->getArtistNames($artists);

            // Loop to make sure that we have the correct artist name - if we search "Depeche Mode", then we filter of "Depeche Mode - Tribute" 
            for ($i = 0; $i <= count($artist_names); $i++) {
                if (strtolower($artist_names[$i]["name"]) == strtolower($this->search_for_artist)) {
                    $id = $artist_names[$i]["id"];
                }
            }
			// Wrong Kashmir - make more smart ...
			if ($id == "spotify:artist:2M6KSMQtb9N2xWrpsvFm7t") {
				$id = "spotify:artist:6Jsq0AbwEKcmDuyA4ca9wu";
			} 
			// Wrong Nephew
			if ($id == "spotify:artist:3rIPPywm3KF56WjcVqDRVG") {
				$id = "spotify:artist:11BUDylkl50Y6dsbZMZiCG";
			} 
			// Wrong Nirvana
			/*if ($id = "spotify:artist:6olE6TJLqED3rqDCT0FyPh") {
				$id = "spotify:artist:7dIxU1XgxBIa3KJAWzaFAC";
			}*/
            $items = (array)$this->lookup($id, "album");
            $items = (array)$items["artist"];
            $albums = (array)$items["albums"];
            $sp_albums = array();

            if (count($albums) > 0) {
                for ($i = 0; $i <= count($albums); $i++) {
                    $album = (array)$albums[$i];
                    $alb = (array)$album["album"];
                    if (strtolower($alb["artist"]) == strtolower($this->search_for_artist)) {
                        $sp_albums[] = $alb["href"];
                    }
                }
            }
            
            $sp_albums = array_unique($sp_albums);
   
            if (count($sp_albums) > 0) {
                $item_list = array();
                for ($i = 0; $i <= count($sp_albums); $i++) {
                    $id = $sp_albums[$i];
                    $tmp_item = $this->lookup($id, $item);
                    $tmp_item = (array)$tmp_item;
                    $album = (array)$tmp_item["album"];
                    $artist_name = $album["artist"];
                    $availability = (array)$album["availability"];
                    $territories = $availability["territories"];
                    if (strstr($territories, $this->record_store_webservice_country) || $territories == "") {
                        if (strtolower($artist_name) == strtolower($this->search_for_artist)) {
                            for ($j=0;$j <=count($album["tracks"]); $j++) {
                                $track = (array)$album["tracks"][$j];
                                $item_name = $track["name"];
                                $buy_at_url = $track["href"];
                                if ($item_name != "" && $buy_at_url != "" && in_array($item_name, $item_list) == false) {
                                    $tracks[] = array("artist_name" => $artist_name, "item_name" => $item_name, "buy_at_url" => $buy_at_url);
                                    $item_list[] = $item_name;
                                }
                            }
                        }
                        
                    }
                }
                $result['results'] = $tracks;
            }
        }

        if ($this->search_for_type == "artist_song" || count($items) == 0) {
        } else {
            if ($this->record_store_webservice_country == "DK") {
                $result = array("results" => $this->getItemData($items, $item, $this->record_store_webservice_country));
            } else {
                $result = array("results" => $this->getItemData($items, $item, "GB"));
            }
            
        }

        /* Get only valid albums/songs */
        return $result;
    }
	
    public function setWebserviceURL()
    {
        /* Not used in this API for now */
        if ($this->search_for_type == "artist_album") {
            $this->record_store_webservice_url = "http://ws.spotify.com/search/1/artist?q=" . rawurlencode($this->search_for_artist);
        } else if ($this->search_for_type == "artist_song") {
            $this->record_store_webservice_url = "http://ws.spotify.com/search/1/artist?q=" . rawurlencode($this->search_for_artist);
        } else if ($this->search_for_type == "album") {
            $this->record_store_webservice_url = "http://ws.spotify.com/search/1/artist?q=" . rawurlencode($this->search_for_artist);
        } else if ($this->search_for_type == "song") {
            $this->record_store_webservice_url = "http://ws.spotify.com/search/1/artist?q=" . rawurlencode($this->search_for_artist);
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
    
    /**
   * The API base URL
   * 
   * @var string
   */
  const API_URL = 'http://ws.spotify.com';

  /**
   * Available detail parameters of the lookup method
   * 
   * @var array
   */
  private static $_extras = array('album', 'albumdetail', 'track', 'trackdetail');

  /**
   * Search a Artist by its name
   * 
   * @param string $name                  Name of an artist
   * @param integer [optional] $page      Page number
   * @return mixed
   */
  public static function searchArtist($name, $page = 1) {
    return self::_makeCall('/search/1/artist', array('q' => $name, 'page' => $page));
  }

  /**
   * Search a Album by its name
   * 
   * @param string $title                 Title of a album
   * @param integer [optional] $page      Page number
   * @return mixed
   */
  public static function searchAlbum($title, $page = 1) {
    return self::_makeCall('/search/1/album', array('q' => $title, 'page' => $page));
  }

  /**
   * Search a Track by its name
   * 
   * @param string $title                 Title of a track
   * @param integer [optional] $page      Page number
   * @return mixed
   */
  public static function searchTrack($title, $page = 1) {
    return self::_makeCall('/search/1/track', array('q' => $title, 'page' => $page));
  }

  /**
   * Looks up for more details
   * 
   * @param string $uri                   Valid Spotify URI
   * @param string [optional] $detail     Detail level of the response
   * @return mixed
   */
  public static function lookup($uri, $detail = null) {
    $params = array('uri' => $uri);
    if (isset($detail) && in_array($detail, self::$_extras)) {
      $params['extras'] = $detail;
    }
    return self::_makeCall('/lookup/1/', $params);
  }

  /**
   * Returns the Spotify URI
   * 
   * @param object $obj                   JSON object returned by a search method
   * @param integer [optional] $count     (Default first one)
   * @return string
   */
  public static function getUri($obj, $count = 0) {
    if (true === is_object($obj)) {
      $array = self::_objectToArray($obj);
      $type = $array['info']['type'] . 's';
      return $array[$type][$count]['href'];
    } else {
      throw new Exception("Error: getUri() - Requires JSON object returned by a search method.");
    }
  }

    /**
   * Returns the Spotify artist names
   * 
   * @param object $obj                   JSON object returned by a search method
   * @return string
   */
  public static function getArtistNames($obj) {
    $names = array();
    if (true === is_object($obj)) {
      $array = self::_objectToArray($obj);
      $type = $array['info']['type'] . 's';
      for ($i=0;$i<=count($array[$type]);$i++) {
        $names[] = array( "name" => $array[$type][$i]['name'], "id" => $array[$type][$i]['href']);
      }
      return $names;
    } else {
      throw new Exception("Error: getArtistNames() - Requires JSON object returned by a search method.");
    }
  }

   /**
   * Returns the Spotify artist names
   * 
   * @param object $obj                   JSON object returned by a search method
   * @return string
   */
    public static function getItemData($obj, $item_type, $lang_code) {
    
        $items = array();
        if (true === is_object($obj)) {
            $array = self::_objectToArray($obj);
            $type = $item_type . "s";

            if ($item_type == "album") {
                $array = $array["artist"][$type];
            } else if ($item_type == "track") {
                $array = $array["tracks"];
            }

            for ($i=0;$i<=count($array);$i++) {
                if ($item_type == "album") {
                    if (strstr($array[$i][$item_type]["availability"]["territories"], $lang_code) || $array[$i][$item_type]["availability"]["territories"] == "") {
                        $items[] = array( "artist_name" => $array[$i][$item_type]["artist"], "item_name" => $array[$i][$item_type]["name"], "buy_at_url" => $array[$i][$item_type]["href"]);
                    }
                } else if ($item_type == "track") {
                    if (strstr($array[$i]["album"]["availability"]["territories"], $lang_code)) {
                        $items[] = array( "artist_name" => $array[$i]["artists"][0]["name"], "item_name" => $array[$i]["name"], "buy_at_url" => $array[$i]["href"]);
                    }
                }
            }
            return $items;
        } else {
            throw new Exception("Error: getItemData() - Requires JSON object returned by a search method.");
        }
    }
  
  /**
   * Convert JSON object to an array
   * 
   * @param object $object                The object to convert
   * @return array
   */
  private static function _objectToArray($object) {
    if (!is_object($object) && !is_array($object)) {
      return $object;
    }
    if (is_object($object)) {
      $object = get_object_vars($object);
    }
    return array_map(array(self, '_objectToArray'), $object);
  }

  /**
   * The call operator
   *
   * @param string $function              API resource path
   * @param array $params                 Request parameters
   * @return mixed
   */
  private static function _makeCall($function, $params) {
    $params = '.json?' . utf8_encode(http_build_query($params));
    $apiCall = self::API_URL.$function.$params;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiCall);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    
    $jsonData = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($jsonData);
  }
    
    
}

?>