<?php

function airplay_shop() {
	$section_path = explode('/', request_uri());
	$record_store_name = $section_path[2];
	$section_q = strpos($record_store_name, '?');
	if ($section_q !== FALSE) {
		$record_store_name = substr($record_store_name, 0, $section_q);
	}
	$sHtml = "";
	$record_store_name = airplay_url_to_name($record_store_name);
	$aData = airplay_shop_get_data($record_store_name);
	$sHtml .= airplay_shops_get_record_store_page_html($aData);
	return $sHtml;
}

function airplay_shops_get_record_store_page_html($aData) {
	$sHtml = "";
	//$sHtml .= "Åbningstider .... Google maps ... fragt ... ";
	//($a["is_in_ap_results"] == 1)  ? $in_ap_results .= 'Er med i live resultater' : $in_ap_results = '-';
	foreach($aData AS $a) {
		$record_store_id = $a["record_store_id"];
		$record_store_name = $a["record_store_name"];
		$record_store_logo = $a["record_store_logo"];
		$record_store_description = $a["record_store_description"];
		$media_format_names = $a["media_format_names"];
		$record_store_type_text = airplay_shops_get_record_store_type_text($a["record_store_type_id"]);
		$selling_type_text = airplay_shops_get_selling_type_text($a["selling_type_id"]);
		
		drupal_set_title( "Pladeforretning - " . $record_store_name );
		
		$sHtml .= "<h1>{$record_store_name}</h1>";
		$sHtml .= "<div id='shop_content'>";

		$sHtml .= "<div class='left'>";
		$sHtml .= "<img src='{$record_store_logo}' border='0' width='150' />";
		$sHtml .= "</div>";
		
		$sHtml .= "<div class='middle'>{$record_store_description}</div>";

		$sHtml .= "<div class='right'>";
		$sHtml .= "<div class='desc' id='shop_wiki'>";
		$sHtml .= "<h3>{$record_store_name}</h3> ";
		$sHtml .= "<div class='subheader'>Media formats:</div> ";
		$sHtml .= "<div>{$media_format_names}</div>";
		$sHtml .= "<div class='subheader'>Butikstype:</div>";
		$sHtml .= "<div>{$record_store_type_text}</div>";
		$sHtml .= "<div class='subheader'>Denne butik sælger:</div>";
		$sHtml .= "<div>{$selling_type_text}</div>";
		$sHtml .= "</div>";
		$sHtml .= "</div>";
		
		$sHtml .= "</div>";
	}
	return $sHtml;
}

function airplay_shops_list() {
	$sHtml = "";
	$filter_name = true;
	$filter_country = false;
	$filter_format = false;
	$filter_type = false;
	$aArtistBeginChar = array();
	
	if (isset($_REQUEST["sc"])) {
		$filter_name = false;
		$filter_country = true;
		$filter_format = false;
		$filter_type = false;
	}
	if (isset($_REQUEST["smf"])) {
		$filter_name = false;
		$filter_country = false;
		$filter_format = true;
		$filter_type = false;
	}
	if (isset($_REQUEST["st"])) {
		$filter_name = false;
		$filter_country = false;
		$filter_format = false;
		$filter_type = true;
	}
	
	$aData = airplay_shops_get_data();
	$aRecordStoreNamesData = airplay_shops_get_record_store_names_data();
	$aCountry = airplay_shops_get_country_data();
	$aMediaFormat = airplay_shops_get_media_format_data();
	$aRecordStoreType = airplay_shops_get_record_store_type_data();
	
	$sHtml .= airplay_shops_tabs_html();
	
	$added_non_number = false;
	foreach($aRecordStoreNamesData AS $a) {
		if (0 == (int)substr($a["record_store_name"], 0, 1)) {
			$aArtistBeginChar[] = mb_strtoupper(substr($a["record_store_name"], 0, 1));
		} else {
			if ($added_non_number == false) {
				$aArtistBeginChar[] = "NUM";
				$added_non_number = true;
			}
		}
	}
	//$sHtml .= "<div itemscope='' itemtype='http://data-vocabulary.org/Breadcrumb' style='float:left;'><a href='/' itemprop='url'><span itemprop='title'>Til Forsiden</span></a></div>";
	//$sHtml .= "<div style='float:left;margin:0px 5px 0px 5px;'> &gt; </div><div itemscope='' itemtype='http://data-vocabulary.org/Breadcrumb'><span itemprop='title'>Pladeforretnings Guide</span></div>";
	//$sHtml .= "<div id='addthis'>";
	//if ($languageCode == "da") { $sHtml .= ap_getAddThis(); }
	//$sHtml .= "</div>";
	
	$sHtml .= "<div id='shop_guide'>";

	// HTML for links under the tab - to show the correct filter.
	$sHtml .= "<div id='filters_text'>";
	($filter_name == true) ? $class = ' selected' : $class = '';
	$sHtml .= "<div><a class='artist$class' href='javascript:void(0);' onClick='jQuery(\"#filters #names\").show();jQuery(\"#filters #countries\").hide();jQuery(\"#filters #media_formats\").hide();jQuery(\"#filters #record_store_type\").hide();'>Navn</a></div>";
	($filter_country == true) ? $class = ' selected' : $class = '';
	$sHtml .= "<div><a class='artist$class' href='javascript:void(0);' onClick='jQuery(\"#filters #names\").hide();jQuery(\"#filters #countries\").show();jQuery(\"#filters #media_formats\").hide();jQuery(\"#filters #record_store_type\").hide();'>Land</a></div>";
	($filter_format == true) ? $class = ' selected' : $class = '';
	$sHtml .= "<div><a class='artist$class' href='javascript:void(0);' onClick='jQuery(\"#filters #names\").hide();jQuery(\"#filters #countries\").hide();jQuery(\"#filters #media_formats\").show();jQuery(\"#filters #record_store_type\").hide();'>Format</a></div>";
	($filter_type == true) ? $class = ' selected' : $class = '';
	$sHtml .= "<div><a class='artist$class' href='javascript:void(0);' onClick='jQuery(\"#filters #names\").hide();jQuery(\"#filters #countries\").hide();jQuery(\"#filters #media_formats\").hide();jQuery(\"#filters #record_store_type\").show();'>Butikstype</a></div>";
	$sHtml .= "</div>";
	
	// All filters
	$sHtml .= "<div id='filters'>";
	
	// Starts with name filter
	($filter_name == true) ? $sHtml .= '<div id=\'names\' >' : $sHtml .= '<div id=\'names\' style=\'display:none;\'>';
	$sHtml .= "<div class='alphabet'>";
	$sHtml .= "<div class='items'>";
	foreach(range('A','Z') as $i) {
		if (in_array($i, $aArtistBeginChar)) {
			(isset($_GET["sn"]) && $_GET["sn"] == $i)  ? $class = ' selected' : $class = '';
			$sHtml .= "<div class='item$class'><a href='?sn={$i}' rel='nofollow'>{$i}</a></div>";
		} else {
			$sHtml .= "<div class='item'>{$i}</div>";
		}
	}
	// TODO Optimize with at loop
	if (in_array("Æ", $aArtistBeginChar)) {
		(isset($_GET["sn"]) && $_GET["sn"] =="Æ")  ? $class = ' selected' : $class = '';
		$sHtml .= "<div class='item$class'><a href='?sn=Æ' rel='nofollow'>Æ</a></div>";
	} else {
		$sHtml .= "<div class='item'>Æ</div>";
	}
	if (in_array("Ø", $aArtistBeginChar)) {
		(isset($_GET["sn"]) && $_GET["sn"] =="Ø")  ? $class = ' selected' : $class = '';
		$sHtml .= "<div class='item$class'><a href='?sn=Ø' rel='nofollow'>Ø</a></div>";
	} else {
		$sHtml .= "<div class='item'>Ø</div>";
	}
	if (in_array("Å", $aArtistBeginChar)) {
		(isset($_GET["sn"]) && $_GET["sn"] =="Å")  ? $class = ' selected' : $class = '';
		$sHtml .= "<div class='item$class'><a href='?sn=Å' rel='nofollow'>Å</a></div>";
	} else {
		$sHtml .= "<div class='item'>Å</div>";
	}
	if (in_array("NUM", $aArtistBeginChar)) {
		(isset($_GET["sn"]) && $_GET["sn"] =="NUM")  ? $class = ' selected' : $class = '';
		$sHtml .= "<div class='item$class'><a href='?sn=NUM' rel='nofollow'>#</a></div>";
	} else {
		$sHtml .= "<div class='item'>#</div>";
	}
	(isset($_GET["sn"]) && $_GET["sn"] =="ALL")  ? $class = ' selected' : $class = '';
	$sHtml .= "<div class='item$class'><a href='?sn=ALL' rel='nofollow'>Alle</a></div>";
	$sHtml .= "</div>";
	$sHtml .= "</div>";
	$sHtml .= "</div>";
	
	// Country filter
	($filter_country == true) ? $sHtml .= '<div id=\'countries\' >' : $sHtml .= '<div id=\'countries\' style=\'display:none;\'>';
	$sHtml .= "<div class='items'>";
	foreach ($aCountry AS $a) {
		(isset($_REQUEST["sc"]) && $_REQUEST["sc"] == $a["country_id"]) ? $class = ' selected' : $class = '';
		$sHtml .= "<div class='item$class'><a href='?sc=" . $a["country_id"] . "' rel='nofollow'>" . t($a["country_name"]) . "</a></div>"; // (" . $a["record_store_count"] . ")
	}
	$sHtml .= "</div>";
	$sHtml .= "</div>";
	
	// Media formats
	($filter_format == true) ? $sHtml .= '<div id=\'media_formats\'>' : $sHtml .= '<div id=\'media_formats\' style=\'display:none;\'>';
	$sHtml .= "<div class='items'>";
	foreach ($aMediaFormat AS $a) {
		(isset($_REQUEST["smf"]) && $_REQUEST["smf"] == $a["media_format_id"]) ? $class = ' selected' : $class = '';
		$sHtml .= "<div class='item$class'><a href='?smf=" . $a["media_format_id"] . "' rel='nofollow'>" . $a["media_format_name"] . "</a></div>";
	}
	// Merchandise...
	$sHtml .= "<div class='item'><a href='?smf=64'>Merchandise</a></div>";
	$sHtml .= "</div>";
	$sHtml .= "</div>";
	
	// Record store types
	($filter_type == true) ? $sHtml .= '<div id=\'record_store_type\'>' : $sHtml .= '<div id=\'record_store_type\' style=\'display:none;\'>';
	$sHtml .= "<div class='items'>";
	foreach ($aRecordStoreType AS $a) {
		(isset($_REQUEST["st"]) && $_REQUEST["st"] == $a["record_store_type_id"]) ? $class = ' selected' : $class = '';
		$sHtml .= "<div class='item$class'><a href='?st=" . $a["record_store_type_id"] . "' rel='nofollow'>" . $a["type_name"] . "</a></div>";
	}
	$sHtml .= "</div>";
	$sHtml .= "</div>";
	
	$sHtml .= "</div>";
	
	// Get all record_store elements HTML
	if (count($aData)) {
		$sHtml .= "<div class='header_text'><h2>Pladeforretninger</h2></div>";
		$sHtml .= "<div id='shopcontainers'>";
		$sHtml .= "<div id='containers'>";
		$sHtml .= "<div class='list_container'>";
		$sHtml .= "<div class='items_header'><div class='shopname'>Pladeforretning</div><div class='format'>Format</div><div class='type'>Type</div><div class='grading'>Nyt / brugt</div><div class='country'>Land</div><div class='link'>&nbsp;</div></div>";
		$sHtml .= "</div>";
		$sHtml .= airplay_shops_record_store_elements_html ($aData);
		$sHtml .= "</div>";
		$sHtml .= "</div>";
	}
	$sHtml .= "</div>";
		
	return $sHtml;
}

function airplay_shops_record_store_elements_html ($aData) {
	$sHtml = "";
	$last_record_store_name = "";
	foreach ($aData AS $a) {
		$record_store_name = $a["record_store_name"];
		$media_format_names = $a["media_format_names"];
		$link_to_record_store = str_replace("[RECORD_STORE_NAME]", $record_store_name, "Gå til [RECORD_STORE_NAME] hjemmeside");
		$link_to_record_store_internal = str_replace("[RECORD_STORE_NAME]", $record_store_name, "Læs mere information om [RECORD_STORE_NAME]");
		$record_store_type_text = airplay_shops_get_record_store_type_text($a["record_store_type_id"]);
		$selling_type_text = airplay_shops_get_selling_type_text($a["selling_type_id"]);
		$country_name = t($a["country_name"]);
		$ap_record_store_url = airplay_name_to_url($record_store_name);
		
		($a["is_in_ap_results"] == 1)  ? $in_ap_results = 1 : $in_ap_results = 0;

		$sHtml .= "<div class='list_container'>";
		//$sHtml .= "<div class='shopname'><a href='/pladeforretning/{$ap_record_store_url}' title='{$link_to_record_store_internal}'>{$record_store_name}</a></div>";
		$sHtml .= "<div class='shopname'>{$record_store_name}</div>";
		$sHtml .= "<div class='format'>{$media_format_names}</div>";
		$sHtml .= "<div class='type'>{$record_store_type_text}</div>";
		$sHtml .= "<div class='grading'>{$selling_type_text}</div>";
		$sHtml .= "<div class='country'>{$country_name}</div>";
		$sHtml .= "<div class='link'>";
		if ($in_ap_results == 1) {
			$sHtml .= "<img src='http://www.airplaymusic.dk/favicon.ico' border='0' width='15' height='15' alt='{$record_store_name} er med i Airplay Music søgeresultater' title='{$record_store_name} er med i Airplay Music søgeresultater' />&nbsp;&nbsp;&nbsp;&nbsp;";
		}
		$sHtml .= "<a href='" . $a["record_store_url"] . "' title='{$link_to_record_store}' target='_blank'><img src='http://www.airplaymusic.dk/images/site/homepage_icon.png' border='0' width='17' height='15' alt='{$record_store_name}' /></a>";
		$sHtml .= "</div>";
		$sHtml .= "</div>";
	}
	return $sHtml;
}

function airplay_shops_get_record_store_type_text($record_store_type_id) {
	if ($record_store_type_id == 2) {
		return "Webshop"; // t("Webshop");
	} else if ($record_store_type_id == 4) {
		return "Fysisk butik"; //t("Recordstore");
	} else if ($record_store_type_id == 8) {
		return "Fysisk butik + Webshop"; //t("Recordstore + Webshop");
	}
}

function airplay_shops_get_selling_type_text($selling_type_id) {
	if ($selling_type_id == 1) {
		return "Nyt"; // t("New");
	} else if ($selling_type_id == 2) {
		return "Brugt"; //t("Used");
	} else if ($selling_type_id == 3) {
		return "Nyt + brugt"; //t("New + used");
	}
}

function airplay_shops_tabs_html () {
	return "<div id='tabs'><h3>Pladeforretninger</h3></div>";
}
