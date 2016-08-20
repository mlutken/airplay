<?php
	/*
	 File for getting data to show recordstore icons on facebook app page.
	*/
	require_once ( '../../../aphp/aphp_fix_include_path.php' );
	require_once ( '../../../aphp/airplay_globals.php' );
	require_once ( '../../../aphp/db_api/FacebookAppDataMySql.php' );
	require_once ( '../functions/FacebookFunctions.php' );

	// Make sure output is JSON
	header('Content-Type: application/json');
	
	// Make sure that this ajax file is only valid from our domain - this way we can minimize folks from "stealing" our service.
    if (isset($_SERVER["HTTP_REFERER"]) && stristr($_SERVER["HTTP_REFERER"], "facebook.airplaymusic.")) {
		$artist_id = 0;
		$item_base_id = 0;
		$output_is_json = 0;
		$item_data = array();
		$ap_facebook_data = new FacebookAppDataMySql($m_dbAll);
		$ap_facebook = new FacebookApplication();
		
		if ($ap_facebook->artist_id != 0 && $ap_facebook->item_type != 0) {

			$ap_facebook_app_data_cache = $ap_facebook_data->getItemBasePriceFacebookAppDataFromCache($ap_facebook->artist_id, $ap_facebook->media_format_id, $ap_facebook->item_type);
			
			if (count($ap_facebook_app_data_cache) > 0) {
				// Cache one day expired
				if ($ap_facebook_app_data_cache[0]["time_span"] > 1440) {

					$ap_facebook_app_data = $ap_facebook_data->getItemBasePriceFacebookAppData($ap_facebook->artist_id, $ap_facebook->media_format_id, $ap_facebook->item_type);
					
					foreach ($ap_facebook_app_data AS $data) {
						$record_store_formatted = $ap_facebook->formatRecordStoreName($data["record_store_name"]);
						$item = array ("item_name" => $data["item_base_name"], "media_format_name" => $data["media_format_name"], "price_local" => $data["price_local"],  "currency_code" => $data["currency_code"],  "buy_at_url" => $data["buy_at_url"], "affiliate_url" => $data["affiliate_link"], "affiliate_encode_times" => $data["affiliate_encode_times"], "record_store_name" => $data["record_store_name"], "record_store_class_name" => $record_store_formatted);
						$item_data["items"][] = array("item" => $item);
					}
					$ap_facebook_app_updated = $ap_facebook_data->updateItemBasePriceFacebookAppData($ap_facebook_app_data_cache[0]["facebook_app_v1_id"], json_encode($item_data));

				} else {

					$ap_facebook_app_data = $ap_facebook_app_data_cache[0]["json"];
					$output_is_json = 1;

				}
			// Not in cache
			} else {

				$ap_facebook_app_data = $ap_facebook_data->getItemBasePriceFacebookAppData($ap_facebook->artist_id, $ap_facebook->media_format_id, $ap_facebook->item_type);
				foreach ($ap_facebook_app_data AS $data) {
					$record_store_formatted = $ap_facebook->formatRecordStoreName($data["record_store_name"]);
					$item = array ("item_name" => $data["item_base_name"], "media_format_name" => $data["media_format_name"], "price_local" => $data["price_local"],  "currency_code" => $data["currency_code"],  "buy_at_url" => $data["buy_at_url"], "affiliate_url" => $data["affiliate_link"], "affiliate_encode_times" => $data["affiliate_encode_times"], "record_store_name" => $data["record_store_name"], "record_store_class_name" => $record_store_formatted);
					$item_data["items"][] = array("item" => $item);
				}
				$ap_facebook_app_inserted = $ap_facebook_data->insertItemBasePriceFacebookAppData($ap_facebook->artist_id, $ap_facebook->media_format_id, $ap_facebook->item_type, json_encode($item_data));
			}

			if ($output_is_json == 0) {
				foreach ($ap_facebook_app_data AS $data) {
					$record_store_formatted = $ap_facebook->formatRecordStoreName($data["record_store_name"]);
					$item = array ("item_name" => $data["item_base_name"], "media_format_name" => $data["media_format_name"], "price_local" => $data["price_local"],  "currency_code" => $data["currency_code"],  "buy_at_url" => $data["buy_at_url"], "affiliate_url" => $data["affiliate_link"], "affiliate_encode_times" => $data["affiliate_encode_times"], "record_store_name" => $data["record_store_name"], "record_store_class_name" => $record_store_formatted);
					$item_data["items"][] = array("item" => $item);
				}
				print json_encode($item_data);
			} else {
				print $ap_facebook_app_data;
			}
		}
	}
?>