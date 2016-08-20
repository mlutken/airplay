<?php
/*
    Dynamic lookup file - is used for getting "live searches" streaming services or large recordstores, which has an API (webservice).
    
    This file is used to generate json and saves it to the database (table live_search_cache - maybe rename ?)
    
    The json format as the following:
    - "Artist-page" - we only save the excact items we have in our database and the total amount of items in the API.
        - search_for_type_id = 1 is albums on artist page
        - search_for_type_id = 2 is songs on artist page
    - "Album-page" - we only save all items we find in the API with this the given name.
        - search_for_type_id = 3 is albums on album page
    - "Song-page" - we only save all items we find in the API with this the given name.
        - search_for_type_id = 4 is songs on song page
        
        
        
        TODO:
        Dont call set functions from the childs contructor.
        Check only empty cache X times otherwise return empty result.

*/

    // Make sure that this ajax file is only valid from our domain - this way we can minimize folks from "stealing" our service.
    if (isset($_SERVER["HTTP_REFERER"]) && stristr($_SERVER["HTTP_REFERER"], "facebook.airplaymusic.")) {

        // Init files and settings
        date_default_timezone_set('Europe/Copenhagen');
        require_once ( '../../../aphp/aphp_fix_include_path.php' );
        require_once ( '../../../aphp/airplay_globals.php' );
        require_once ( '../../../aphp/dynamic_music_lookup/DynamicMusicFactory.php' );
        require_once ( '../../../aphp/dynamic_music_lookup/utils.php' );
        require_once ( '../../../aphp/db_api/SimpleTableDataMySql.php' );
        require_once ( '../../../aphp/db_api/ItemDynamicMusicMySql.php' );
        require_once ( '../../../aphp/db_api/db_string_utils.php' );
        require_once ( '../../../aphp/db_api/ItemDataMySql.php' );
        require_once ( '../../../aphp/db_api/ArtistDataMySql.php' );
        require_once ( '../../../aphp/db_api/RecordStoreDataMySql.php' );

        // Get the record_store_name
        $record_store_name = getRecordStoreNameFromURL();

        if ($record_store_name != "") {
            // Init Factory and create the correct object
            $ap_dynamic_music_search = new DynamicMusicFactory();
            $dynamic_music_provider = $ap_dynamic_music_search->createDynamicMusicSearchProvider($record_store_name);
        }

        // Make sure we have a valid webservice address - otherwise dont do anything
        if ($dynamic_music_provider != null) {
            
            // Set variables we need
            $dynamic_music_provider->setWebserviceURL();

            // Make sure that we have a artist to search for.
            if ($dynamic_music_provider->search_for_artist != "") {
            
                $ap_dynamic_music_lookup = new ItemDynamicMusicMySql( $m_dbAll );
                
                // See if we have a record in the cache.
                $ap_dynamic_music_cache_id = $ap_dynamic_music_lookup->getAPILiveSearchID($dynamic_music_provider->artist_id, $dynamic_music_provider->item_base_id, $dynamic_music_provider->record_store_id, $dynamic_music_provider->search_for_type_id, $dynamic_music_provider->record_store_webservice_country);

                // Search is not in cache
                if ($ap_dynamic_music_cache_id == 0) { 
                    $result = $dynamic_music_provider->getDataFromWebservice();

                    // If 1 or 3 - then this is an album.
                    if ($dynamic_music_provider->search_for_type_id == 1 || $dynamic_music_provider->search_for_type_id == 3) {
                        $item_type = 1;
                    // Song.
                    } else {
                        $item_type = 2;
                    }
                    
                    // Get all item names for an artist
                    $o_item_base = new ItemDataMySql( $m_dbAll );
                    $a_item_names = $o_item_base->getItemNamesForArtist($dynamic_music_provider->artist_id, $item_type );
                    
                    // Album / songs
                    if ($dynamic_music_provider->search_for_type_id == 3 || $dynamic_music_provider->search_for_type_id == 4) {
                        $a_item_names = "";
                        $a_item_names[] = array ("item_base_name" => $dynamic_music_provider->search_for_words);
                    }
                    
                    $response = $dynamic_music_provider->parseDataFromWebservice($result, $a_item_names);

                    $json_response = "";
                    $json_response_collapsed = "";
                    if (isset($response["response_collapsed"]) && count($response["response_collapsed"]) > 0) {
                        $json_response_collapsed = json_encode($response["response_collapsed"]);
                        $dynamic_music_provider->outputJSON($response["response_collapsed"]);
                    } else if (isset($response["response"]) && count($response["response"]) > 0) {
                        $json_response = json_encode($response["response"]);
                        $dynamic_music_provider->outputJSON($response["response"]);
                    } else {
                        $dynamic_music_provider->outputJSON(array("items" => ""));
                    }
                    
                    $item_count = (int)$response["item_count"];

                    if (isset($response["response_all_items"]) && count($response["response_all_items"]) > 0) {
                        $dynamic_music_provider->ItemsToImportXML($response["response_all_items"]);
                    }
                    // Save data.
                    if ($json_response != "" || $json_response_collapsed != "") {
                        $ap_dynamic_music_lookup->createNew ($dynamic_music_provider->artist_id, $dynamic_music_provider->item_base_id, $dynamic_music_provider->search_for_type_id, $dynamic_music_provider->record_store_id, $json_response, $json_response_collapsed, $item_count, $dynamic_music_provider->record_store_webservice_country);
                    }
                } else {
                
                    $ap_dynamic_music_cache_datetime = $ap_dynamic_music_lookup->getAPILiveSearchCachedAgo($ap_dynamic_music_cache_id);

                    // Cache expired 2880 min = 2 days
                    if ($ap_dynamic_music_cache_datetime["TimeSpan"] > 2880) {
                    
                        $result = $dynamic_music_provider->getDataFromWebservice();
                        
                        // Get all item names for an artist
                        $o_item_base = new ItemDataMySql( $m_dbAll );
                        $a_item_names = $o_item_base->getItemNamesForArtist($dynamic_music_provider->artist_id, $item_type );
                        
                        // Album / songs
                        if ($dynamic_music_provider->search_for_type_id == 3 || $dynamic_music_provider->search_for_type_id == 4) {
                            $a_item_names = "";
                            $a_item_names[] = array ("item_base_name" => $dynamic_music_provider->search_for_words);
                        }
                        
                        $response = $dynamic_music_provider->parseDataFromWebservice($result, $a_item_names);
                        
                        $json_response = "";
                        $json_response_collapsed = "";
                        if (isset($response["response_collapsed"]) && count($response["response_collapsed"]) > 0) {
                            $json_response_collapsed = json_encode($response["response_collapsed"]);
                            $dynamic_music_provider->outputJSON($response["response_collapsed"]);
                        } else if (isset($response["response"]) && count($response["response"]) > 0) {
                            $json_response = json_encode($response["response"]);
                            $dynamic_music_provider->outputJSON($response["response"]);
                        }
                        
                        $data = array("live_search_cache_id" => $ap_dynamic_music_cache_id, "json_response" => $json_response, "json_response_collapsed" => $json_response_collapsed);

                        // Update old data.
                        $ap_dynamic_music_lookup->updateBaseData( $data );

                    }

                    // Cache not expired - get data.
                    else {
                    
                        $aData = $ap_dynamic_music_lookup->getBaseData($ap_dynamic_music_cache_id);
                        if ($aData["json_response"] <> "" || $aData["json_response_collapsed"] <> "") {
                            if ($aData["json_response"] <> "") {
                                $dynamic_music_provider->outputJSON(array( "json_response" => $aData["json_response"]));
                            } else if ($aData["json_response_collapsed"] <> "") {
                                $dynamic_music_provider->outputJSON(array( "json_response_collapsed" => $aData["json_response_collapsed"]));
                            }
                        // Ajax SLOW if no output.
                        } else {
                            print " ";
                        }
                    }
                }
            }
        }
    }
?>