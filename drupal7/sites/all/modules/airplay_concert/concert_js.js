function ap_concert_show_artist_drop_down() {
	jQuery("#filters #artists_container").show();
	jQuery("#filters #concerts").hide();
	jQuery("#filters #intervals").hide();
	
	jQuery("#filters_text .artist").removeClass('selected');
	jQuery("#filters_text .artist").addClass('selected');
	jQuery("#filters_text .venue").removeClass('selected');
	jQuery("#filters_text .event_date").removeClass('selected');

};

function ap_concert_show_concert_drop_down() {
	jQuery("#filters #concerts").show();
	jQuery("#filters #artists_container").hide();
	jQuery("#filters #intervals").hide();
	
	jQuery("#filters_text .artist").removeClass('selected');
	jQuery("#filters_text .venue").removeClass('selected');
	jQuery("#filters_text .venue").addClass('selected');
	jQuery("#filters_text .event_date").removeClass('selected');

};

function ap_concert_show_interval_drop_down() {
	jQuery("#filters #intervals").show();
	jQuery("#filters #artists_container").hide();
	jQuery("#filters #concerts").hide();
	
	jQuery("#filters_text .artist").removeClass('selected');
	jQuery("#filters_text .venue").removeClass('selected');
	jQuery("#filters_text .event_date").removeClass('selected');
	jQuery("#filters_text .event_date").addClass('selected');
};

function ap_concert_select_artist(artist_id, element) {
	var classname = jQuery(element).attr('class');
	classname = classname.replace('item ', '');
	if ( classname == "selected") {
		jQuery(element).removeClass('selected');
	} else {
		jQuery(element).addClass('selected');
	}
	
	if( jQuery.inArray(artist_id, aSelectedArtists) == -1 ) {
		aSelectedArtists.push( artist_id );
	} else {
		if (aSelectedArtists.indexOf(artist_id) != -1) {
			aSelectedArtists.splice(aSelectedArtists.indexOf(artist_id), 1);
		}
	}
	ap_concert_reset_artist_layout();
	ap_concert_reset_concert_layout();
};

function ap_concert_reset_artist_layout() {
	var count = aSelectedArtists.length;
	if (count > 0) {
		jQuery('#containers div.items div.item').each(function(index, value){
			jQuery(this).hide();
		});
		for (var j = 0; j < count; j++) {
			jQuery('#containers div.items div.item').each(function(index, value){
				var className = jQuery(this).attr('class');
				className = className.replace('item artist_', '');
				className = className.replace(' even', '');
				className = className.replace(' odd', '');
				if (className == aSelectedArtists[j]) {
					jQuery(this).show();
				}
			});
		}
	} else {
		jQuery('#containers div.items div.item').each(function(index, value){
			jQuery(this).show();
		});
	}
};

function ap_concert_reset_concert_layout() {
	var count = 0;
	jQuery('#containers div.items div.item').each(function(index, value){
		jQuery(this).removeClass('even');
		jQuery(this).removeClass('odd');
		if (jQuery(this).is(':visible')) {
			if ((count%2) == 0) {
				jQuery(this).addClass('even');
			} else {
				jQuery(this).addClass('odd');
			}
			count++;
		}
	});
};
