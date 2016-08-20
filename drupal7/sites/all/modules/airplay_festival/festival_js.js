function ap_festival_show_artist_drop_down() {
	jQuery("#artists_container").show();
	jQuery("#festivals").hide();
	jQuery("#filters_text a.festival").removeClass('selected');
	jQuery("#filters_text a.artist").addClass('selected');
};

function ap_festival_show_festival_drop_down() {
	jQuery("#festivals").show();
	jQuery("#artists_container").hide();
	jQuery("#filters_text a.artist").removeClass('selected');
	jQuery("#filters_text a.festival").addClass('selected');
};

function ap_festival_show_hide_map() {
	if (jQuery("#map_container").is(":visible") && jQuery("#map_container").height() == 600) {
		ap_festival_hide_map();
	} else {
		jQuery("#map_container").css("height", "600px");
		ap_festival_show_map();
	}
};

function ap_festival_show_map() {
	jQuery("#map_container").show();
};

function ap_festival_hide_map() {
	jQuery("#map_container").hide();
};

function ap_festival_select_artist(artist_id, element) {
	var dbg = true;
	if (dbg == true) {
		var start = new Date().getTime();
	}
	/* Reset festival */
	aSelectedFestival = [];
	ap_festival_add_festival_to_selected_filter();
	ap_festival_highlight_festivals_in_filter();
	
	/* Artist filter */
	ap_festival_make_selected_artists_array(artist_id, jQuery(element).text());
	ap_festival_highlight_artists_in_filter();
	ap_festival_add_artist_to_selected_filter();
	
	/* Artist tables */
	ap_festival_hightlight_artists();
	ap_festival_open_festival_containers();
	ap_festival_rearrange_icons_on_map();
	
	if (dbg == true) {
		var end = new Date().getTime();
		var time = end - start;
		console.log('Execution time (select artist): ' + time);
	}
};


function ap_festival_remove_artist(artist_id, element) {
	if (artist_id == 0) {
		aSelectedArtist = [];
		ap_festival_add_artist_to_selected_filter();
	} else {
		jQuery(element).remove();
		ap_festival_remove_artist_from_selected_artists_array(artist_id);
	}
	/* Artist filter */
	ap_festival_highlight_artists_in_filter();
	ap_festival_hightlight_artists();
	/* Artist tables */
	ap_festival_open_festival_containers();
	ap_festival_rearrange_icons_on_map();
};


function ap_festival_select_festival(record_store_id, festival_name) {
	var dbg = true;
	if (dbg == true) {
		var start = new Date().getTime();
	}
	/* Reset artist */
	aSelectedArtist = [];
	ap_festival_highlight_artists_in_filter();
	ap_festival_add_artist_to_selected_filter();
	
	/* Festival filter */
	ap_festival_make_selected_festival_array(record_store_id, festival_name);
	ap_festival_highlight_festivals_in_filter();
	ap_festival_add_festival_to_selected_filter();
	
	/* Artist tables */
	//ap_festival_open_festival_containers();
	ap_festival_map_to_container_sort();
	ap_festival_show_elements_in_container();
	
	if (dbg == true) {
		var end = new Date().getTime();
		var time = end - start;
		console.log('Execution time (select festival): ' + time);
	}
};

function ap_festival_remove_festival(record_store_id, element) {
	if (record_store_id == 0) {
		aSelectedFestival = [];
		ap_festival_add_festival_to_selected_filter();
	} else {
		jQuery(element).remove();
		ap_festival_remove_festival_from_selected_festivals_array(record_store_id);
	}
	ap_festival_highlight_festivals_in_filter();
	ap_festival_open_festival_containers();
	ap_festival_show_elements_in_container();
	ap_festival_add_artist_to_selected_filter();
};


function ap_festival_show_elements_in_container() {
	jQuery("#containers div.list_container div.theitems").hide();
	for (var i = 0; i < FestivalCount; i++) {
		for (var j = 0; j < aSelectedFestival.length; j++) {
			if (jQuery( '#container_'  + i + " div.header div.name").text() == aSelectedFestival[j][1]) {
				id = jQuery( '#container_'  + i).attr("id").replace("container_", "");
				jQuery("#container_" + id + " div.theitems").show();
			}
		}
	}
};


function ap_festival_open_container(id) {
	jQuery("#container_" + id + " div.theitems").show();
};


function ap_festival_make_selected_artists_array(artist_id, artist_name) {
	var count = aSelectedArtist.length;
	var tempIndex = -1;
	for (var i = 0; i < count; i++) {
		if (aSelectedArtist[i][0] == artist_id) {
			tempIndex = i;
			break;
		}
	}
	if (tempIndex == -1) {
		var arr = new Array(artist_id, artist_name);
		aSelectedArtist.push( arr );
	} else {
		aSelectedArtist.splice(tempIndex, 1);
	}
};


function ap_festival_make_selected_festival_array(record_store_id, festival_name) {
	var count = aSelectedFestival.length;
	var tempIndex = -1;
	for (var i = 0; i < count; i++) {
		if (aSelectedFestival[i][0] == record_store_id) {
			tempIndex = i;
			break;
		}
	}
	if (tempIndex == -1) {
		var arr = new Array(record_store_id, festival_name);
		aSelectedFestival.push( arr );
	} else {
		aSelectedFestival.splice(tempIndex, 1);
	}
};


function ap_festival_remove_artist_from_selected_artists_array(artist_id) {
	var count = aSelectedArtist.length;
	var tempIndex = -1;
	for (var i = 0; i < count; i++) {
		if (aSelectedArtist[i][0] == artist_id) {
			tempIndex = i;
			break;
		}
	}
	if (tempIndex != -1) {
		aSelectedArtist.splice(tempIndex, 1);
	}
};


function ap_festival_remove_festival_from_selected_festivals_array(record_store_id) {
	var count = aSelectedFestival.length;
	var tempIndex = -1;
	for (var i = 0; i < count; i++) {
		if (aSelectedFestival[i][0] == record_store_id) {
			tempIndex = i;
			break;
		}
	}
	if (tempIndex != -1) {
		aSelectedFestival.splice(tempIndex, 1);
	}
};


function ap_festival_highlight_artists_in_filter() {
	jQuery("#artists div.items div.item").removeClass('selected');
	var count = aSelectedArtist.length;
	if (count > 0) {
		for (var i = 0; i < count; i++) {
			jQuery("#artists div.items div.item_" + aSelectedArtist[i][0]).addClass('selected');
		}
	}
};

function ap_festival_highlight_festivals_in_filter() {
	jQuery("#festivals div.items div.item").removeClass('selected');
	var count = aSelectedFestival.length;
	if (count > 0) {
		for (var i = 0; i < count; i++) {
			jQuery("#festivals div.items div.item_" + aSelectedFestival[i][0]).addClass('selected');
		}
	}
};

function ap_festival_add_artist_to_selected_filter() {
	jQuery("#selected_filters div.artists div.items").html("");
	var count = aSelectedArtist.length;
	var html = "";
	if (count > 0) {
		html += '<div class="item selected" onClick="ap_festival_remove_artist(0, this);">Alle</div>';
		for (var i = 0; i < count; i++) {
			html += '<div class="item selected" onClick="ap_festival_remove_artist(' + aSelectedArtist[i][0] + ', this);">' + aSelectedArtist[i][1] + '</div>';
		}
	} else {
		html += '<div class="item selected" onClick="ap_festival_remove_artist(0, this);">Alle</div>';
	}
	jQuery("#selected_filters div.artists div.items").append(html);
};


function ap_festival_add_festival_to_selected_filter() {
	jQuery("#selected_filters div.festivals div.items").html("");
	var count = aSelectedFestival.length;
	var html = "";
	if (count > 0) {
		html += '<div class="item selected" onClick="ap_festival_remove_festival(0, this);">Alle</div>';
		for (var i = 0; i < count; i++) {
			html += '<div class="item selected" onClick="ap_festival_remove_festival(' + aSelectedFestival[i][0] + ', this);">' + aSelectedFestival[i][1] + '</div>';
		}
	} else {
		html += '<div class="item selected" onClick="ap_festival_remove_festival(0, this);">Alle</div>';
	}
	jQuery("#selected_filters div.festivals div.items").append(html);
};


function ap_festival_rearrange_icons_on_map() {
	for (var i = 0; i < MapFestivals.length; i++) {
		if (arrMarkers[i]) {
			arrMarkers[i].setIcon(iconBase + 'google_marker_festival.png');
		}
	}
	for (var i = 0; i < aContainers.length; i++) {
		if (aContainers[i][1] > 0) {
			jQuery("#container_" + aContainers[i][0] + " div.theitems").show();
			if (arrMarkers[aContainers[i][0]]) {
				arrMarkers[aContainers[i][0]].setIcon(iconBase + 'google_marker_selected.png');
			}
		} else {
			jQuery("#container_" + aContainers[i][0] + " div.theitems").hide();
		}
	}
};


function ap_festival_hightlight_artists() {
	var count = aSelectedArtist.length;
	/* Remove all styles */
	jQuery("div.items div.artist").removeClass('selected');

	for (var i = 0; i < count; i++) {
		artist_id = aSelectedArtist[i][0];
		for (var k = 0; k < aAllArtists.length; k++) {
			if (aAllArtists[k][0] == artist_id) {
				jQuery("div.items div.artist_" + artist_id).addClass('selected');
				aContainers[aAllArtists[k][3]][1] = aContainers[aAllArtists[k][3]][1] + 1;
			}
		}
	}
/*
	var count = aSelectedArtist.length;
	for (var j = 0; j < FestivalCount; j++) {
		var container_id = '#container_' + j;
		var obj = jQuery('div.item', container_id);
		aContainers[j][1] = 0;
		obj.removeClass('selected');
		for (var i = 0; i < count; i++) {
			artist_id = aSelectedArtist[i][0];
			obj.each(function(index, value){
				var classname = jQuery(this).attr('class');
				var needle = 'item artist_' + artist_id;
				if (classname == needle) {
					jQuery(this).addClass('selected');
					aContainers[j][1] = aContainers[j][1] + 1;
				}
			});
		}
	}
	ap_festival_sort_items_in_container();
*/
};


function ap_festival_open_festival_containers() {
	for (var i = 0; i < aContainers.length; i++) {
		if (aContainers[i][1] > 0) {
			jQuery("#container_" + aContainers[i][0] + " div.theitems").show();
		} else {
			jQuery("#container_" + aContainers[i][0] + " div.theitems").hide();
		}
	}
};


function ap_festival_sort_containers() {
	//jQuery('div.list_container', '#containers').sort(ap_festival_sort_container).appendTo('#containers');
	ap_festival_open_festival_containers();
};


function ap_festival_map_to_container_sort() {
	//jQuery('div.list_container', '#containers').sort(ap_festival_sort_container_from_map).appendTo('#containers');
	for (var i = 0; i < MapFestivals.length; i++) {
		if (arrMarkers[i]) {
			arrMarkers[i].setIcon(iconBase + 'google_marker_festival.png');
		}
	}
	for (var i = 0; i < MapFestivals.length; i++) {
		for (var j = 0; j < aSelectedFestival.length; j++) {
			if (arrMarkers[i]["title"] == aSelectedFestival[j][1]) {
				arrMarkers[i].setIcon(iconBase + 'google_marker_selected.png');
				aContainers[i][1] = 1;
			} else {
				aContainers[i][1] = 0;
			}
		}
	}
	ap_festival_open_festival_containers();
};


function ap_festival_select_icon(linkID) {
	for (var i = 0; i < MapFestivals.length; i++) {
		arrMarkers[i].setIcon(iconBase + 'google_marker_festival.png');
	}
	arrMarkers[linkID].setIcon(iconBase + 'google_marker_selected.png');
	jQuery("#map_container").get(0).scrollIntoView();
};


function ap_festival_sort_items_in_container() {
	/*for (var j = 0; j < FestivalCount; j++) {
		var container_id = '#container_' + j;
		jQuery('div.items div.item', container_id).sort(ap_festival_sort_items).appendTo(container_id + ' div.items');
	}*/
	ap_festival_sort_containers();
};


function ap_festival_sort_container_from_map(a, b) {
	var valuea = 0;
	var valueb = 0;
	var texta = jQuery(a).children("div.header").children("div.name").text();
	var textb = jQuery(b).children("div.header").children("div.name").text();
	for (var i = 0; i < aSelectedFestival.length; i++) {
		if (texta == aSelectedFestival[i][1]) {
			valuea = 1;
			break;
		}
		if (textb == aSelectedFestival[i][1]) {
			valueb = 1;
			break;
		}
	}
	return valueb > valuea ? 1 : -1;
};


function ap_festival_sort_container(a, b) {
	var valuea = 0;
	var valueb = 0;
	var tmpa = jQuery(a).attr('id');
	var tmpb = jQuery(b).attr('id');
	tmpa = tmpa.replace('container_', '');
	tmpb = tmpb.replace('container_', '');
	for (var i = 0; i < FestivalCount; i++) {
		if (tmpa == i) {
			valuea = aContainers[i][1];
		}
		if (tmpb == i) {
			valueb = aContainers[i][1];
		}
	}
	if (valuea == 1 && valueb == 1) {
		// Alphabetic order
		var tmpa = jQuery(a).children('div.header').children('div.month').text();
		var tmpb = jQuery(b).children('div.header').children('div.month').text();
		if (tmpa > tmpb) {
			valueb = 2;
		}
	}
	return valueb > valuea ? 1 : -1;
};


function ap_festival_sort_items(a, b) {
	var class1 = jQuery(a).attr('class');
	if (class1.toLowerCase().indexOf('selected') >= 0) {
		class1 = 1;
	} else {
		class1 = 0;
	}
	var class2 = jQuery(b).attr('class');
	if (class2.toLowerCase().indexOf('selected') >= 0) {
		class2 = 1;
	} else {
		class2 = 0;
	}
	if (class1 == 1 && class2 == 1) {
		// Alphabetic order
		var namea = jQuery(a).children('div.artist').children('a').text();
		var nameb = jQuery(b).children('div.artist').children('a').text();
		if (namea > nameb) {
			class2 = 2;
		}
	}
	return class2 > class1 ? 1 : -1;
};


function initialize() {
	// Create an array of styles.
	var styles = [
	{
		stylers: [
			/*{ hue: "#00ffe6" },
			{ lightness: -25 },
			{ saturation: -97 }*/
		]
		},{
			featureType: "poi",
			elementType: "geometry",
			stylers: [
				{ visibility: "off" }
			]
		},{
			featureType: "road",
			elementType: "geometry",
			stylers: [
				{ visibility: "off" }
			]
		},{
			featureType: "road",
			elementType: "labels",
			stylers: [
				{ visibility: "off" }
			]
		},{
			featureType: "landscape",
			elementType: "geometry",
			stylers: [
				{ color: "#fffffa" }
			]
		},{
			featureType: "administrative",
			stylers: [
				{ visibility: "on" },
				{ lightness: 33 }
			]
		}
	];
	
	var styledMap = new google.maps.StyledMapType(styles, {name: "Kort"});

	if (is_mobile()) {
		var mapOptions = {
			center: new google.maps.LatLng(56.244, 10.397),
			zoom: 6,
			mapTypeControlOptions: {
				mapTypeIds: [google.maps.MapTypeId.ROADMAP, 'map_style']
			}
		};
	} else {
		var mapOptions = {
			center: new google.maps.LatLng(56.244, 10.397),
			zoom: 7,
			mapTypeControlOptions: {
				mapTypeIds: [google.maps.MapTypeId.ROADMAP, 'map_style']
			}
		};
	}
  
	var map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);

	map.mapTypes.set('map_style', styledMap);
	map.setMapTypeId('map_style');
	
	// Try HTML5 geolocation
	if(navigator.geolocation) {
		navigator.geolocation.getCurrentPosition(function(position) {
			var pos = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
			var marker = new google.maps.Marker({ position: pos, map: map, title: 'Din placering.' , icon: iconBase + 'google_marker_you.png' });
		}, function() {
			handleNoGeolocation(true);
		});
	} else {
		// Browser doesn't support Geolocation
		handleNoGeolocation(false);
	}
	for (var i = 0; i < MapFestivals.length; i++) {
		var contentString = ap_festival_get_info_window_html_content(MapFestivals[i]);
		/* now inside your initialise function */
		var myinfowindow  = new google.maps.InfoWindow({
			content: contentString
		});
		var myLatlng = new google.maps.LatLng(MapFestivals[i][0], MapFestivals[i][1]);
		var marker = new google.maps.Marker({
			position: myLatlng,
			map: map,
			title: '' + MapFestivals[i][2] + '',
			icon: iconBase + 'google_marker_festival.png',
			infowindow: myinfowindow
		});
		arrMarkers.push(marker);
		arrInfowindow.push(myinfowindow);
		google.maps.event.addListener(marker, 'click', function() {
			//ap_festival_map_to_container_sort(this.getTitle());
			this.setIcon(iconBase + 'google_marker_selected.png');
			ap_festival_close_all_infowindows();
			this.infowindow.open(map, this);
		 });
	}
};


function handleNoGeolocation(errorFlag) {
	if (errorFlag) {
		var content = 'Error: The Geolocation service failed.';
	} else {
		var content = 'Error: Your browser doesn\'t support geolocation.';
	}
	var options = {
		map: map,
		position: new google.maps.LatLng(56.444, 10.397),
		content: content
	};
	var infowindow = new google.maps.InfoWindow(options);
	map.setCenter(options.position);
};


function ap_festival_close_all_infowindows() {
	var count = MapFestivals.length;
	for (var i = 0; i < count; i++) {
		arrInfowindow[i].close();
	}
};


function ap_festival_get_info_window_html_content(content) {
	var html = '<div class="content">';
	html += '<div class="header"><h1>'+ content[2] + '</h1></div>';
	html += '<div class="body_content">';
	if (content[4] != "") {
		html += '<p><b>Dato:</b> ' + content[4] + '</p>';
	}
	html += '<p><a href="' + content[3] + '" target="_blank">' + content[3] + '</a></p>';
	html += '<p>Program: <a href="javascript:void(0);" onClick="jQuery(\'#container_' + content[5] + '\')[0].scrollIntoView(true);ap_festival_open_container(' + content[5] + ');">vis kunstnere</a></p>';
	html += '</div>';
	html += '</div>';
	return html;
};