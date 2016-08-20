function ap_user_settings_change_firstname(firstname) {
	if (firstname != "") {
		jQuery.ajax({type: 'POST', url: '/airplay_user_settings/user_settings_ajax', data: { firstname: firstname } });
		jQuery("div.user_settings div.left div.status_firstname div.no").addClass("ok");
		jQuery("div.user_settings div.left div.status_firstname div.no").removeClass("no");
	}
};

function ap_user_settings_change_lastname(lastname) {
	if (lastname != "") {
		jQuery.ajax({type: 'POST', url: '/airplay_user_settings/user_settings_ajax', data: { lastname: lastname } });
		jQuery("div.user_settings div.left div.status_lastname div.no").addClass("ok");
		jQuery("div.user_settings div.left div.status_lastname div.no").removeClass("no");
	}
};

function ap_user_settings_change_birthday() {
	if (jQuery("#birthday").val().substr(2,1) == "/") {
		var date_formatted = jQuery("#birthday").val().substr(6,4) + "-" + jQuery("#birthday").val().substr(3,2) + "-" + jQuery("#birthday").val().substr(0,2);
	} else {
		var date_formatted = jQuery("#birthday").val();
	}
	if (ap_user_settings_validate_date_format(date_formatted) == true) {
		jQuery.ajax({type: 'POST', url: '/airplay_user_settings/user_settings_ajax', data: { birthday: date_formatted } });
		jQuery("div.user_settings div.left div.status_birthday div.no").addClass("ok");
		jQuery("div.user_settings div.left div.status_birthday div.no").removeClass("no");
	} else {

	}
};

function ap_user_settings_change_gender_layout(gender) {

	jQuery("div.user_settings div.left div.gender_male").removeClass("selected");
	jQuery("div.user_settings div.left div.gender_male").removeClass("unselected");
	jQuery("div.user_settings div.left div.gender_female").removeClass("selected");
	jQuery("div.user_settings div.left div.gender_female").removeClass("unselected");
	
	if ( gender == 1 ) {
		jQuery("div.user_settings div.left div.gender_male").addClass("selected");
		jQuery("div.user_settings div.left div.gender_female").addClass("unselected");
	} else {
		jQuery("div.user_settings div.left div.gender_female").addClass("selected");
		jQuery("div.user_settings div.left div.gender_male").addClass("unselected");
	}
	jQuery.ajax({type: 'POST', url: '/airplay_user_settings/user_settings_ajax', data: { gender: gender } });
};


function ap_user_settings_change_user_country() {
	jQuery.ajax({type: 'POST', url: '/airplay_user_settings/user_settings_ajax', data: { user_country: jQuery("#user_country").val() } });
	jQuery("div.user_settings div.left div.status_country div.no").addClass("ok");
	jQuery("div.user_settings div.left div.status_country div.no").removeClass("no");
};



function ap_user_settings_validate_date_format(date) {
	var regEx = /^\d{4}-\d{2}-\d{2}$/;
	return date.match(regEx) != null;
};


function ap_user_settings_change_fan(artist_id, fan_type) {
	jQuery.ajax({type: 'POST', url: '/airplay_user_settings/user_settings_ajax', data: { aid: artist_id, fan: fan_type } });
	if (fan_type == 3) {
		ap_user_settings_remove_artist_profile(artist_id);
	} else if (fan_type == 2) {
		jQuery("#profile_artist_" + artist_id).appendTo("#profile_artists_ap div.profile_artists");
		ap_user_settings_update_menu_items_after_move(artist_id);
		ap_user_hide_profile_artists_fan_no_fans();
	}
};

function ap_user_settings_update_menu_items_after_move(artist_id) {
	//html = "<div onclick='ap_user_settings_change_fan(" + artist_id + ", 2)" style='width:50%;' class='fan'>Opgradere</div><div style='width:50%;'class='social_media'>Facebook</div></div>";
	html = "<div onclick='ap_user_settings_change_fan(" + artist_id + ", 3)' style='width:100%;' class='delete'>Slet</div>";
	jQuery("#profile_artist_" + artist_id + " div.menu_items").html( html );
};

/*
function ap_user_settings_delete_fan(artist_id) {
	jQuery.ajax({type: 'POST', url: '/airplay_user_settings/user_settings_ajax', data: { aid: artist_id, remove: 'fan' } });
};*/

function ap_user_settings_update_newsletter(status) {

	jQuery("div.user_settings div.left div.newsletter_yes").removeClass("selected");
	jQuery("div.user_settings div.left div.newsletter_yes").removeClass("unselected");
	jQuery("div.user_settings div.left div.newsletter_no").removeClass("selected");
	jQuery("div.user_settings div.left div.newsletter_no").removeClass("unselected");

	if (status == 1) {
		jQuery.ajax({type: 'POST', url: '/airplay_user_settings/user_settings_ajax', data: { newsletter: 1 } });
		jQuery("div.user_settings div.left div.newsletter_yes").addClass("selected");
		jQuery("div.user_settings div.left div.newsletter_no").addClass("unselected");
	} else {
		jQuery.ajax({type: 'POST', url: '/airplay_user_settings/user_settings_ajax', data: { newsletter: 0 } });
		jQuery("div.user_settings div.left div.newsletter_no").addClass("selected");
		jQuery("div.user_settings div.left div.newsletter_yes").addClass("unselected");
	}
};

function ap_user_settings_update_email() {
	/*jQuery("div.user_settings div.left div.newsletter_yes").removeClass("selected");
	jQuery("div.user_settings div.left div.newsletter_yes").removeClass("unselected");
	jQuery("div.user_settings div.left div.newsletter_no").removeClass("selected");
	jQuery("div.user_settings div.left div.newsletter_no").removeClass("unselected");*/

	if (ap_user_settings_validate_email() == false) {
		alert("Fejl i mail-adresse");
	} else if (ap_user_settings_validate_email()) {
		jQuery.ajax({type: 'POST', url: '/airplay_user_settings/user_settings_ajax', data: { email: jQuery("#email").val() } });
//		jQuery("div.user_settings div.left div.newsletter_yes").addClass("selected");
//		jQuery("div.user_settings div.left div.newsletter_no").addClass("unselected");
		jQuery("div.user_settings div.left div.status_email div.no").addClass("ok");
		jQuery("div.user_settings div.left div.status_email div.no").removeClass("no");
	}
};

function ap_user_settings_validate_email() {
	var email = jQuery("#email").val();
    var filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	if (!filter.test(email)) {
		jQuery("#email").focus();
		return false;
	} else {
		return true;
	}
};

function ap_user_settings_remove_artist_profile(artist_id) {
	if (jQuery("#profile_artist_" + artist_id).length) {
		jQuery("#profile_artist_" + artist_id).remove();
	}
};

function ap_user_settings_autocomplete() {
	if (jQuery("#add_artist_to_profile").val().length >= 2) {
		jQuery.ajax({type: 'POST', url: '/airplay_user_settings/user_settings_ajax', data: { search: jQuery("#add_artist_to_profile").val() }, dataType: 'json', success: ap_user_settings_populate_autocomplete });
	}
	return true;
};

function ap_user_settings_populate_autocomplete(json) {
	var autocomplete_item = "";
	jQuery.each(json, function(i, artists) {
		jQuery.each(artists, function(j, item) {
			if (item.artist_name != "") {
				autocomplete_item += "<li><div onClick='ap_user_settings_create_artist_element(" + item.artist_id + ", \"" + item.artist_name + "\");'>" + item.artist_name + "</div></li>";
			}
			jQuery("#user_settings_artist_autocomplete").html(autocomplete_item);
		})
	});
	jQuery("#user_settings_artist_autocomplete").show();
};

function ap_user_settings_create_artist_element(artist_id, artist_name) {
	if (!jQuery("#profile_artist_" + artist_id).length) {
		jQuery.ajax({type: 'POST', url: '/airplay_user_settings/user_settings_ajax', data: { create_artist_element: artist_id, artist_name: artist_name}, dataType: 'html', success: function(html) {
			jQuery("#profile_artists_ap .profile_artists").after( html )
			ap_user_settings_change_fan(artist_id, 2);
			ap_user_hide_profile_artists_fan_no_fans();
        } });
	}
	jQuery("#user_settings_artist_autocomplete").hide();
};

function ap_user_hide_profile_artists_fan_no_fans() {
	jQuery("div.profile_artists_text").hide();
};

function ap_user_show_profile_artists_fan_no_fans() {
	jQuery("div.profile_artists_text").show();
};


function ap_user_settings_price_agent_delete_agent( agent_id ) {
	ap_user_settings_price_agent_reset_delete_agents();

	jQuery("#agent_" + agent_id + " div.delete div").removeClass("active");
	jQuery("#agent_" + agent_id + " div.delete div").removeClass("inactive");
	jQuery("#agent_" + agent_id + " div.delete div").addClass("active");
	jQuery.ajax({type: 'POST', url: '/airplay_price_agent/price_agent_ajax', data: { agent: 'confirm_delete_agent', id: agent_id }, dataType: 'html', success: ap_price_agent_update_modal_html });
	ap_user_settings_price_agent_set_layer_position("#agent_" + agent_id + " div.delete div");
};

function ap_user_settings_price_agent_confirm_delete_agent( agent_id, confirm ) {
	
	if (confirm == true) {
		ap_user_settings_price_agent_postback_delete_agent(agent_id);
	}
	setTimeout(function() { jQuery('#price_agent_container').hide(); }, 100);
	ap_user_settings_price_agent_reset_delete_agents();
	
};

function ap_user_settings_price_agent_postback_delete_agent( agent_id ) {
	jQuery.ajax({type: 'POST', url: '/airplay_price_agent/price_agent_ajax', data: { agent: 'postback_delete_agent', id: agent_id }, dataType: 'html' });
	jQuery('#agent_' + agent_id).hide();
};

function ap_user_settings_price_agent_reset_delete_agents() {
	jQuery("div.music_agents div.delete div").each(function() {
		jQuery(this).removeClass("active");
		jQuery(this).removeClass("inactive");
		jQuery(this).addClass("inactive");
	});
};

function ap_user_settings_price_agent_edit_album_agent( agent_id, item_base_id ) {
	ap_user_settings_price_agent_reset_edit_agents();
	jQuery("#agent_" + agent_id + " div.edit div").removeClass("active");
	jQuery("#agent_" + agent_id + " div.edit div").removeClass("inactive");
	jQuery("#agent_" + agent_id + " div.edit div").addClass("active");
	jQuery.ajax({type: 'POST', url: '/airplay_price_agent/price_agent_ajax', data: { agent: 'edit_agent', id: agent_id, ibid: item_base_id }, dataType: 'html', success: ap_price_agent_update_modal_html });
	
	ap_user_settings_price_agent_set_layer_position("#agent_" + agent_id + " div.edit div");
};

function ap_user_settings_price_agent_reset_edit_agents() {
	jQuery("div.music_agents div.edit div").each(function() {
		jQuery(this).removeClass("active");
		jQuery(this).removeClass("inactive");
		jQuery(this).addClass("inactive");
	});
};

function ap_user_settings_price_agent_edit_concert_agent( agent_id ) {
	ap_user_settings_price_agent_reset_edit_agents();
	jQuery("#agent_" + agent_id + " div.edit div").removeClass("active");
	jQuery("#agent_" + agent_id + " div.edit div").removeClass("inactive");
	jQuery("#agent_" + agent_id + " div.edit div").addClass("active");
	jQuery.ajax({type: 'POST', url: '/airplay_price_agent/price_agent_ajax', data: { agent: 'edit_agent', id: agent_id }, dataType: 'html', success: ap_price_agent_update_modal_html });

	ap_user_settings_price_agent_set_layer_position("#agent_" + agent_id + " div.edit div");
	
};

function ap_user_settings_price_agent_set_layer_position(offset_element) {
	var offset = ((jQuery("#tabs").position().top-jQuery(offset_element).position().top)*-1) + 32;
	jQuery("#tabs #price_agent_container").css('top', offset);
	jQuery("#tabs #price_agent_container").css('right', 60);
};

function ap_user_settings_artist_image_error(artist_id, artist_name, caller) {
	if (caller != "JS") {
		google.setOnLoadCallback(function() {
			ap_user_settings_get_google_image(artist_id, artist_name);
		});
	} else {
		ap_user_settings_get_google_image(artist_id, artist_name);
	}
};

function ap_user_settings_artist_image_error_from_js(artist_id, artist_name) {
	ap_user_settings_get_google_image(artist_id, artist_name);
};

function ap_user_settings_get_google_image(artist_id, artist_name) {
    var imageSearch = new google.search.ImageSearch();
    // Restrict image size: IMAGESIZE_SMALL , IMAGESIZE_MEDIUM, IMAGESIZE_LARGE, IMAGESIZE_EXTRA_LARGE
    imageSearch.setRestriction(google.search.ImageSearch.RESTRICT_IMAGESIZE, google.search.ImageSearch.IMAGESIZE_MEDIUM); 
    imageSearch.setResultSetSize(1); // Only load 1 image 
    imageSearch.setSearchCompleteCallback(this, ap_user_settings_parseGoogleImageDataAndLoadFirst, [imageSearch, artist_id, artist_name]);
    imageSearch.execute(artist_name);
};

function ap_user_settings_parseGoogleImageDataAndLoadFirst(data, artist_id, artist_name) 
{
	if (data.results && data.results.length > 0) {
		var results = data.results;
		var result = results[0];
		jQuery('#artist_image_id_' + artist_id).attr('src',result.tbUrl);
	}
};

function ap_user_settings_report_error_in_image(type, artist_id) {
	jQuery.ajax({type: 'POST', url: '/airplay_user_settings/user_settings_ajax', data: { report_error: 1, type: type, artist_id: artist_id }, dataType: 'html' });
};
