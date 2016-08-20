<?php
require_once ("dynamic_music_lookup/DynamicMusicFactory.php");

class BaseDynamicMusic
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( )
    {
    }

    public function setRecordStoreID($record_store_name) {
        $o_record_store = new RecordStoreDataMySql( $m_dbAll );
        $this->record_store_id = $o_record_store->nameToIDSimple($record_store_name);
    }
    
    public function setRecordStoreData($record_store_name) {
        $o_record_store = new RecordStoreDataMySql( $m_dbAll );
        $this->record_store_id = $o_record_store->nameToIDSimple($record_store_name);
        $o_record_store = $o_record_store->getBaseData($this->record_store_id);
        if ($o_record_store["use_affiliate"]) {
            $this->record_store_affiliate_url = $o_record_store["affiliate_link"];
            $this->record_store_affiliate_encode_times = $o_record_store["affiliate_encode_times"];
        } else {
            $this->record_store_affiliate_url = "";
            $this->record_store_affiliate_encode_times = "";
        }
    }
    
    public function setSearchForItemname()
    {
        if (isset($_REQUEST["q"])) { 
            $this->search_for_words = $_REQUEST["q"];
        } else {
            $this->search_for_words = "";
        }
    }
    
    public function setSearchForArtistname()
    {
        if (isset($_REQUEST["qa"])) { 
            $this->search_for_artist = $_REQUEST["qa"];
        } else {
            $this->search_for_artist = "";
        }
    }
    
    public function setSearchForArtistID()
    {
        if (isset($_REQUEST["aid"])) { 
            $this->artist_id = $_REQUEST["aid"];
        } else {
            $this->artist_id = "";
        }
    }
    
    public function setSearchForItemBaseID()
    {
        if (isset($_REQUEST["iid"])) { 
            $this->item_base_id = $_REQUEST["iid"];
        } else {
            $this->item_base_id = 0;
        }
    }
    
    public function setSearchForCountryCode()
    {
        if (isset($_REQUEST["c"])) { 
            $this->record_store_webservice_country = $_REQUEST["c"];
        } else {
            $this->record_store_webservice_country = "";
        }
    }
    
    public function setSearchForType()
    {
        if (isset($_REQUEST["t"])) { 
            $this->search_for_type = $_REQUEST["t"];
            if ($this->search_for_type == "artist_album") {
                $this->search_for_type_id = 1;
            } else if ($this->search_for_type == "artist_song") {
                $this->search_for_type_id = 2;
            } else if ($this->search_for_type == "album") {
                $this->search_for_type_id = 3;
            } else if ($this->search_for_type == "song") {
                $this->search_for_type_id = 4;
            }
        } else {
            $this->search_for_type = "";
            $this->search_for_type_id = 0;
        }
    }
    
    
    /*
        Sets the url for the webservice to make sure that we call songs on song page, album on album page.
    */
    public function setWebserviceURL()
    {
        printf("Implement me in deirved"); exit(1);
    }
    
    /*
        Return the data from that webservice as a native array.
    */
    public function getDataFromWebservice()
    {
        printf("Implement me in deirved"); exit(1);
    }
    
    /*
        Test if item found is the same as in array
    */
    public function isItemNameFoundInMasterData($item_name, &$array){ 
        foreach($array as $key => $value) { 
            if (strtolower($value["item_base_name"]) == strtolower($item_name)) { return true; }; 
        } 
        return false; 
    } 
    
    /*
        Test if item found is the same as in array
    */
    public function isItemNameInBeginningOfMasterData($item_name, &$array) { 
        foreach($array as $key => $value) { 
            if (strtolower(substr($item_name,0, strlen($value["item_base_name"]))) == strtolower($value["item_base_name"])) { return true; }; 
        } 
    } 
    
    /*
        Convert the native array from provider to our json format.
        // Result - native array
        // $a_item_names - our song, album names
        Artist page only need item_name, price_local, currency_code, record_store_id
    */
    public function parseDataFromWebservice($result, &$a_item_names)
    {
        printf("Implement me in deirved"); exit(1);
    }
    
    /*
        Remove site specific text ex. remove " - Single" form iTunes albums.
    */
    public function removeRecordSpecificTextFromItem($item_name) {
        //if ($this->record_store == "itunes") {
            if (substr($item_name, -3) == " EP") {
                $item_name = substr($item_name, 0, -3);
            } else if (substr($item_name, -7) == " Single") {
                $item_name = substr($item_name, 0, -7);
            }
        //}
        return $item_name;
    }
    
    /*
        Output the correct json format.
        Collapsed is on artist pages.
        Non-collapsed is on item pages.
    */
    public function outputJSON($json_data)
    {
      //  header('Content-Type: text/json');
		if ($json_data["json_response_collapsed"] <> "") {
            print $json_data["json_response_collapsed"];
        } else if ($json_data["json_response"] <> "") {
            print $json_data["json_response"];
        } else {
            print json_encode($json_data);
        }
    }

    /*
        Function used to output array from dynamic til our ap XML files.
    */
    public function ItemsToImportXML($array) {
        $xml_nodes = "";
        //print "<pre>";
       //var_dump($array);
        $item_type = $array["item_count"][0]["type"];
        $file_path = __DIR__;
        $file_path = str_replace("aphp/dynamic_music_lookup", "filesupload/priority_upload/files", $file_path);
        
        if (count($array["items"]) > 0) {
            for ($i=0; $i < count($array["items"]); $i++) {
				if ($array["items"][$i]["item"]["artist_name"] != "") {
					$file_name = $file_path . "/" . strtolower($array["items"][$i]["item"]["artist_name"]) . "_" . $item_type . ".xml";
					if ($i == 0) {
						$xml_nodes .= '<?xml version="1.0" encoding="UTF-8"?>';
					}
$xml_nodes .= "\n<title>
<data_record_type>" . $item_type . "</data_record_type>
<" . $item_type . "_name><![CDATA[" . $array["items"][$i]["item"]["item_name"] ."]]></" . $item_type . "_name>
<artist_name><![CDATA[" . $array["items"][$i]["item"]["artist_name"] ."]]></artist_name>
<media_type_name>audio</media_type_name>
</title>\n";
				}
			}
$xml = "<ROOT>\n" . $xml_nodes . "</ROOT>";
			if ($file_name != "") {
				file_put_contents($file_name, $xml);
			}
		}

    }
    
    // ---------------------
    // --- PRIVATE: Data --- 
    // ---------------------
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