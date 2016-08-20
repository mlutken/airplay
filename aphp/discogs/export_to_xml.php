<?php 

	mb_internal_encoding("UTF-8");

	require_once ( '../aphp_fix_include_path.php' );
	require_once ('../airplay_globals.php');

	require_once ('../utils/general_utils.php');
	require_once ('../utils/string_utils.php');
	require_once ('../db_api/db_string_utils.php');
	require_once ('../db_manip/AllDbTables.php');

	$dba = new AllDbTables();

	/*
	foreach(range('A','Z') as $char) {
		
		$export = new exportData();
		$dat = $export->export($char);
		
		print "getting data for {$char} ....\n";
		
		$output_xml_file = "test_{$char}.xml";
		
		@unlink($output_xml_file);

		if (count($dat)) {
			file_put_contents($output_xml_file, $export->GetRootStartXML() , FILE_APPEND);
			$found_items_count = 1;
			foreach ($dat AS $a) {
				file_put_contents($output_xml_file, $export->GetTitleXMLElement($a["title"], $a["name"], $found_items_count) , FILE_APPEND);
				$found_items_count++;
			}
			file_put_contents($output_xml_file, $export->GetRootEndXML() , FILE_APPEND);
		}
	}*/
	
	
	foreach(range('0','9') as $char) {
		
		$export = new exportData();
		$dat = $export->export($char);
		
		print "getting data for {$char} ....\n";
		
		$output_xml_file = "test_{$char}.xml";
		
		@unlink($output_xml_file);

		if (count($dat)) {
			file_put_contents($output_xml_file, $export->GetRootStartXML() , FILE_APPEND);
			$found_items_count = 1;
			foreach ($dat AS $a) {
				if (!stristr($a["name"], " (")) {
					file_put_contents($output_xml_file, $export->GetTitleXMLElement($a["title"], $a["name"], $found_items_count) , FILE_APPEND);
					$found_items_count++;
				}
			}
			file_put_contents($output_xml_file, $export->GetRootEndXML() , FILE_APPEND);
		}
	}
	
	
	
	
	
	
	
	
	class exportData {

		public function export($letter)
		{
			$Data = $this->getDataToExport($letter);
			return $Data;
		}

		function getDataToExport($letter)
		{
			global $g_MySqlPDO;
			$q = "SELECT DISTINCT discogs_artists.name, discogs_masters.title  FROM discogs_releases
					INNER JOIN discogs_masters ON discogs_masters.master_id = discogs_releases.master_id
					INNER JOIN discogs_artist_masters_rel ON discogs_artist_masters_rel.master_id = discogs_masters.master_id
					INNER JOIN discogs_artists ON discogs_artists.artist_id = discogs_artist_masters_rel.artist_id
					WHERE /*LOWER(substring(discogs_artists.name, 1,1)) = :letter*/ start_char = :letter AND format_desc = 'Album' AND discogs_releases.data_quality = 'Correct' AND discogs_masters.data_quality = 'Correct'
					/* AND discogs_artists.data_quality = 'Correct'*/	";
			$a = pdoQueryAssocRows($g_MySqlPDO, $q, array( ":letter" => $letter ) );
			return $a;
		}
		
		public function GetRootStartXML()
		{
			$s = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<ROOT>\n";
			return $s;
		}
		
		public function GetRootEndXML()
		{
			$s = "\n</ROOT>";
			return $s;
		}
		
		public function GetTitleXMLElement($item_name,  $artist_name, $found_items_count)
		{
			$s = "
			<title>
				<data_record_type>album</data_record_type>
				<album_name><![CDATA[{$item_name}]]></album_name>
				<artist_name><![CDATA[{$artist_name}]]></artist_name>
				<item_master>1</item_master>
				<item_base_reliability>60</item_base_reliability>
				<dbg_state>SearchListing({$found_items_count}): OptionIndex: </dbg_state>
			</title>\n";
			return $s;
		}

	}


?>