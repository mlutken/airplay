<?php

require_once ("dynamic_music_lookup/BaseDynamicMusic.php");
require_once ("dynamic_music_lookup/AmazonECS.class.php");



class Amazon extends BaseDynamicMusic
{
    public function __construct( $artist_id, $search_for_words, $search_for_artist, $search_for_type, $record_store_webservice_country )
    {
        parent::__construct( $artist_id, $search_for_words, $search_for_artist, $search_for_type, $record_store_webservice_country );
        $this->record_store_id = 6;
    }
	
    public function getDataFromWebservice()
    {
        defined('AWS_API_KEY') or define('AWS_API_KEY', 'AKIAINR5QBNIPPMDTRDQ');
        defined('AWS_API_SECRET_KEY') or define('AWS_API_SECRET_KEY', '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');
        defined('AWS_ASSOCIATE_TAG') or define('AWS_ASSOCIATE_TAG', 'airpmusi-21');
                
        $amazonEcs = new AmazonECS(AWS_API_KEY, AWS_API_SECRET_KEY, 'co.uk', AWS_ASSOCIATE_TAG);
        // Pages 
        $pages = 0;
        // for the new version of the wsdl its required to provide a associate Tag
        // @see https://affiliate-program.amazon.com/gp/advertising/api/detail/api-changes.html?ie=UTF8&pf_rd_t=501&ref_=amb_link_83957571_2&pf_rd_m=ATVPDKIKX0DER&pf_rd_p=&pf_rd_s=assoc-center-1&pf_rd_r=&pf_rd_i=assoc-api-detail-2-v2
        // you can set it with the setter function or as the fourth paramameter of ther constructor above
        $amazonEcs->associateTag(AWS_ASSOCIATE_TAG);

        // changing the category to DVD and the response to only images and looking for some matrix stuff.
        // We use - category : 'MP3Downloads','Music','MusicTracks'
        // We use - responseGroup: 'ListItems', 'Offers', 'OfferFull', 'OfferListings', 'Tracks'
        $result = (array)$amazonEcs->country('co.uk')->category('MP3Downloads')->optionalParameters(array('Artist' => $this->search_for_artist, 'Title' => $this->search_for_words, 'Sort' => '-price', 'MerchantId' => 'Amazon'))->responseGroup('OfferFull')->page(1)->search(''); 
              print "<pre>";
              var_dump($result);
        $result = (array)$result["Items"];
                              //  var_dump($result);
        $result = (array)$result["Item"];
       // $result = (array)$result["Offers"];
              //  $result = (array)$result;

//var_dump($result);
        for ( $i=0; $i <= count($result); $i++) {
            $item = (array)$result[$i];
                        var_dump($item);
            $item = (array)$item["Offers"];
            print "/*****************************************/";


          // var_dump($item);
            if ($this->search_for_type == "artist_album" || $this->search_for_type == "album") {
                $results[] = array("item_name" => "", "artist_name" => "", "buy_at_url" => $item["MoreOffersUrl"], "price_local" => $item["Offer"]["OfferListing"]["Price"]["Amount"]);
            } else if ($this->search_for_type == "artist_song" || $this->search_for_type == "song") {
                $results[] = array("item_name" => "", "artist_name" => "", "buy_at_url" => $item["MoreOffersUrl"], "price_local" => $item["Offer"]["OfferListing"]["Price"]["Amount"]);
            }
        }
        
        return $results;
    }
	
    public function parseDataFromWebservice($result, &$a_item_names)
    {
    print "<pre>";
  //  var_dump($result);
        $response = array();
        $response_collapsed = array();
        if (count($result) > 0) {
            for ( $i=0; $i <= count($result); $i++) {
            print $result[$i]["Offers"]["MoreOffersUrl"];
                if ($this->search_for_type == "artist_album") {
                   //$item_name = parent::removeRecordSpecificTextFromItem($result[$i]["title"]);
                    $item_name = $result[$i]["album"]["title"];
                    $item_name = cleanItemNameFull($item_name, $m_dbAll->m_dbItemBaseCorrection );
                    if ($this->isItemNameFoundInMasterData($item_name, $a_item_names) == true) {
                        if ( $result[$i]["artist"]["name"] == $this->search_for_artist) {
                            $item = array("artist_name" => $result[$i]["artist"]["name"], "item_name" => $item_name, "buy_at_url" => $result[$i]["link"], "price_local" => 0, "currency_code" => "DKK", "media_format_name" => "Streaming", "record_store_name" => "Amazon (DK)");
                            $response_collapsed["items"][] = array("item" => $item);
                        }
                    }
                } else if ($this->search_for_type == "artist_song") {
                    //$item_name = parent::removeRecordSpecificTextFromItem($result[$i]["title"]);
                    $item_name = $result[$i]["title"];
                    $item_name = cleanItemNameFull($item_name, $m_dbAll->m_dbItemBaseCorrection );
                    if ($this->isItemNameFoundInMasterData($item_name, $a_item_names) == true) {
                        if ( $result[$i]["artist"]["name"] == $this->search_for_artist) {
                            $item = array("artist_name" => $result[$i]["artist"]["name"], "item_name" => $item_name, "buy_at_url" => $result[$i]["link"], "price_local" => 0, "currency_code" => "DKK", "media_format_name" => "Streaming", "record_store_name" => "Amazon (DK)");
                            $response["items"][] = array("item" => $item);
                        }
                    }
                } else if ($this->search_for_type == "album") {
                    //$item_name = parent::removeRecordSpecificTextFromItem($result[$i]["title"]);
                    $item_name = $result[$i]["Offers"]["title"];
                    if ($this->isItemNameInBeginningOfMasterData($item_name, $a_item_names) == true) {
                        if ( $result[$i]["artist"]["name"] == $this->search_for_artist) {
                            $item = array("artist_name" => $result[$i]["artist"]["name"], "item_name" => $item_name, "buy_at_url" => $result[$i]["link"], "price_local" => 0, "currency_code" => "DKK", "media_format_name" => "Streaming", "record_store_name" => "Amazon (DK)");
                            $response_collapsed["items"][] = array("item" => $item);
                        }
                    }
                } else if ($this->search_for_type == "song") {
                    //$item_name = parent::removeRecordSpecificTextFromItem($result[$i]["title"]);
                    $item_name = $result[$i]["title"];
                    if ($this->isItemNameInBeginningOfMasterData($item_name, $a_item_names) == true) {
                        $item_name = cleanItemNameFull($item_name, $m_dbAll->m_dbItemBaseCorrection );
                        if ( $result[$i]["artist"]["name"] == $this->search_for_artist) {
                            $item = array("artist_name" => $result[$i]["artist"]["name"], "item_name" => $item_name, "buy_at_url" => $result[$i]["link"], "price_local" => 0, "currency_code" => "DKK", "media_format_name" => "Streaming", "record_store_name" => "Amazon (DK)");
                            $response["items"][] = array("item" => $item);
                        }
                    }
                }
            }
        }
        return array("response" => $response, "response_collapsed" => $response_collapsed);
    }
    
    public function setWebserviceURL()
    {
        if ($this->search_for_type == "artist_album") {
            $this->record_store_webservice_url = "http://api.Amazon.com/2.0/search?output=json&order=RANKING&q=" . $this->search_for_artist;
        } else if ($this->search_for_type == "artist_song") {
            $this->record_store_webservice_url = "http://api.Amazon.com/2.0/search?output=json&order=RANKING&q=" . $this->search_for_artist;
        } else if ($this->search_for_type == "album") {
            $this->record_store_webservice_url = "http://api.Amazon.com/2.0/search?output=json&order=RANKING&q=" . $this->search_for_words;
        } else if ($this->search_for_type == "song") {
            $this->record_store_webservice_url = "http://api.Amazon.com/2.0/search?output=json&order=RANKING&q=" . $this->search_for_words;
        }
    }
}
?>