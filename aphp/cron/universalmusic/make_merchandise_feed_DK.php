<?php

	require_once ( __DIR__ . '/../../aphp_fix_include_path.php' );
	require_once ('airplay_globals.php');
	require_once ('../../utils/general_utils.php');
	require_once ('../../utils/string_utils.php');
	require_once ('db_api/ArtistDataMySql.php');
	require_once ('db_api/UniversalMusicDataMySql.php');

	$webservice_artist_names = array();
	$file_path = __DIR__;
	$currency_code = 'DKK';
	$country_code = 'DK';
	$item_type = 3;
	$progress_count = 1;
	$g_fileDbPrettyJson = false;
	
	$result = array();
	$item = array();
	$items = array();
	$media_format = array();
	$media_formats = array();
	$aMedia_format_ids = array();
	$record_store = array();
	$record_stores = array();
	$aRecord_store_ids = array();
	
	$file_name = "merchandise_current_{$country_code}.json";
	$file_path = str_replace("aphp/cron/universalmusic", "public_files/partners/universalmusic/", $file_path);

	try {

		$oArtist 		= new ArtistDataMySql;
		$item_data 	= new UniversalMusicDataMySql;
		
		foreach ($item_data->universal_char_list as $char) {
			$res = $item_data->getArtistNameFromWebservice($char);
			foreach($res["data"] AS $a) {
				$webservice_artist_names[] = $a;
			}
		}
		
		// Make sure that we only have unique artists .
		$webservice_artist_names = array_unique($webservice_artist_names, SORT_REGULAR);
		
		if (count($webservice_artist_names) > 0) {
			foreach($webservice_artist_names AS $a) {
				$artist_name = $a["name"];
				$uuid = $a["uuid"];
				$artist_name = trim($artist_name);
				$universal_artists[] = array( "artist_name" => $artist_name, "uuid" => $uuid) ;
			}
		} else {
			throw new Exception('Universal Music - merchandise script - no artist in master file.');
		}

		if (count($universal_artists) > 0) {
			foreach ($universal_artists As $aArtist) {
				$artist_id = $oArtist->lookupID( $aArtist["artist_name"] );
				if ($progress_count % 100 == 0 ) {
					printf("Progress[%d]: '%s'\n", $progress_count, $aArtist['artist_name']);
				}

				$progress_count++;

				if ($artist_id > 0) {
					$data = $item_data->getUniversalMusicDataByItemType($artist_id, $item_type, $currency_code);

					foreach($data AS $a) {
						if ($aArtist['artist_name'] == $a["artist_name"]) {
							$buy_at_url = $a['buy_at_url'];
							// Encode if needed.
							if ($a['use_affiliate'] == 1) {
								$buy_at_url = $item_data->ap_replace_affiliate_link($buy_at_url, $a["affiliate_link"], $a["affiliate_encode_times"]);
							}
							
							$price = $a["price_local"];
							$price_local_formatted = $item_data->airplay_format_price($price, 'DKK');
							$price_local = $item_data->airplay_format_price($price, 'GBP');
							$uuid = $aArtist["uuid"];
							
							$record_store_id = $a["record_store_id"];
							$shipping_cost = 0;
							$shipping_cost_formatted = $item_data->airplay_format_price($shipping_cost, 'DKK');
							if ($record_store_id == 23) {
								$shipping_cost = "49.00";
								$shipping_cost_formatted = $item_data->airplay_format_price($shipping_cost, 'DKK');
							} else if ($record_store_id == 9) {
								$shipping_cost = "36.00";
								$shipping_cost_formatted = $item_data->airplay_format_price($shipping_cost, 'DKK');
							} else if ($record_store_id == 28) {
								$shipping_cost = "47.50";
								$shipping_cost_formatted = $item_data->airplay_format_price($shipping_cost, 'DKK');
							} else if ($record_store_id == 31) {
								$shipping_cost = "49.95";
								$shipping_cost_formatted = $item_data->airplay_format_price($shipping_cost, 'DKK');
							}
							if ($a["country_id"] == 45) {
								$country_code = "DK";
							}
							$data = array ("ap_item_price_id" => $a["item_price_id"], "uuid" => $uuid, "artist_name" => $a["artist_name"], "name" => $a["item_price_name"], "picture_path" => $a["cover_image_url"], "price_local" => $price_local, "price_local_formatted" => $price_local_formatted, "currency_code" => $currency_code,  "deeplink" => $buy_at_url, "category_id" => $a["media_format_id"], "category_name" => $a["media_format_name"], "shop_id" => $record_store_id, "shop_name" => $a["record_store_name"], "shop_country_code" => $country_code, "shop_country_name" => $a["country_name"], "shipping_cost" => $shipping_cost, "shipping_cost_formatted" => $shipping_cost_formatted);
							$item[] = array("item" => $data);
							
							$item_data->makeRecordStoreArray($a["record_store_id"], $a["record_store_name"], $country_code, $a["country_name"], $shipping_cost, $shipping_cost_formatted);
							$item_data->makeMediaFormatArray($a["media_format_id"], $a["media_format_name"]);

						}
					}
				}
			}
			
			$items = array("items" => $item);
			$record_stores = array("shops" => $record_store);
			$media_formats = array("categories" => $media_format);
			
			$result = array( "results" => array($items, $media_formats, $record_stores));

			writeFileDbFile($file_path . $file_name, $result);
			$item_data->copyFileToBackup($file_path, $file_name, 3);
			$item_data->GZipJSONFile($file_path . $file_name);
			$item_data->copyFileToBackup($file_path, $file_name. ".gz", 3);

		} else {
			throw new Exception('Universal Music - merchandise script - no artist names in master file.');
		}

	} catch (Exception $e) {
		echo 'Caught exception: ',  $e->getMessage(), "\n";
	}
?>