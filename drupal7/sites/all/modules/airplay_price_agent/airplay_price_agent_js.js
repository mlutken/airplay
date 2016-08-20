/************* CONCERT **************/

function ap_price_agent_create_concert_agent(artist_id) {
	jQuery.ajax({type: 'POST', url: '/airplay_price_agent/price_agent_ajax', data: { aid: artist_id, agent: 'get_create_concert_form' }, dataType: 'html', success: ap_price_agent_update_modal_html });
};

function ap_price_agent_create_concert_agent_postback(artist_id) {
	if (jQuery("#agent_mid").val() != -1) {
		jQuery.ajax({type: 'POST', url: '/airplay_price_agent/price_agent_ajax', data: { aid: artist_id, agent: 'save_concert_price_agent', mid: jQuery("#agent_mid").val() }, dataType: 'html', success: ap_price_agent_update_modal_html });
	} else {
		alert("Du mangler at angive kriterier for din Agent.");
	}
};
function ap_price_agent_edit_concert_agent_postback(agent_id, artist_id) {
	if (jQuery("#agent_mid").val() != -1) {
		jQuery.ajax({type: 'POST', url: '/airplay_price_agent/price_agent_ajax', data: { aid: artist_id, id: agent_id, agent: 'save_edit_concert_price_agent', mid: jQuery("#agent_mid").val() }, dataType: 'html', success: ap_price_agent_update_modal_html });
	} else {
		alert("Du mangler at angive kriterier for din Agent.");
	}
};



/************* ALBUM **************/

function ap_price_agent_create_album_agent(artist_id, item_base_id) {
	jQuery.ajax({type: 'POST', url: '/airplay_price_agent/price_agent_ajax', data: { aid: artist_id, ibid: item_base_id, agent: 'get_create_album_form' }, dataType: 'html', success: ap_price_agent_update_modal_html });
};

function ap_price_agent_create_album_agent_postback(artist_id, item_base_id) {
	var checkValues = [];
	jQuery("input[name=price_agent_media_format]:checked").map(function(){
		checkValues.push(jQuery(this).val());
	});
	if (checkValues.length > 0 && isNaN(jQuery('#agent_price').val() / 1) == false) {
		jQuery.ajax({type: 'POST', url: '/airplay_price_agent/price_agent_ajax', data: { aid: artist_id, ibid: item_base_id, agent: 'save_album_price_agent', price: jQuery("#agent_price").val(), mid: checkValues }, dataType: 'html', success: ap_price_agent_update_modal_html });
	} else {
		alert("Du mangler at angive kriterier for din Agent.");
	}
};

function ap_price_agent_edit_album_agent_postback(artist_id, agent_id, item_base_id) {
	var checkValues = [];
	jQuery("input[name=price_agent_media_format]:checked").map(function(){
		checkValues.push(jQuery(this).val());
	});
	if (checkValues.length > 1 && isNaN(jQuery('#agent_price').val() / 1) == false) {
		jQuery.ajax({type: 'POST', url: '/airplay_price_agent/price_agent_ajax', data: { id: agent_id, aid: artist_id, ibid: item_base_id, agent: 'save_edit_album_price_agent', price: jQuery("#agent_price").val(), mid: checkValues }, dataType: 'html', success: ap_price_agent_update_modal_html });
	} else {
		alert("Du mangler at angive kriterier for din Agent.");
	}
};

function ap_price_agent_send_validate_mail() {
	jQuery.ajax({type: 'POST', url: '/airplay_price_agent/price_agent_ajax', data: { agent: 'send_validate_mail' }, dataType: 'html', success: ap_price_agent_update_modal_html });
};

function ap_price_agent_update_modal_html(html) {
	jQuery( '#price_agent_container' ).html( jQuery( html ).filter( '#response' ).html() );
	jQuery( '#price_agent_container' ).show();
};

function ap_price_agent_select_concert_format(media_format) {

	var selectedValue = jQuery('#agent_mid').val();
	if (selectedValue == media_format && selectedValue > 0) {
		jQuery('#agent_mid').val(-1);
	} else if (selectedValue != media_format && selectedValue > 0) {
		jQuery('#agent_mid').val(0);
	} else if (selectedValue == 0 && media_format == 128) {
		jQuery('#agent_mid').val(129);
	} else if (selectedValue == 0 && media_format == 129) {
		jQuery('#agent_mid').val(128);
	} else {
		jQuery('#agent_mid').val(media_format);
	}
	ap_price_agent_set_concert_format_icons(jQuery('#agent_mid').val());
};

function ap_price_agent_set_concert_format_icons(media_format) {
	jQuery("#price_agent_container div.right div.128").removeClass("selected");
	jQuery("#price_agent_container div.right div.129").removeClass("selected");
	jQuery("#price_agent_container div.right div.128").removeClass("unselected");
	jQuery("#price_agent_container div.right div.129").removeClass("unselected");
	if (media_format == -1) {
		jQuery("#price_agent_container div.right div.128").addClass("unselected");
		jQuery("#price_agent_container div.right div.129").addClass("unselected");
	} else if (media_format == 128) {
		jQuery("#price_agent_container div.right div.128").addClass("selected");
		jQuery("#price_agent_container div.right div.129").addClass("unselected");
	} else if (media_format == 129) {
		jQuery("#price_agent_container div.right div.129").addClass("selected");
		jQuery("#price_agent_container div.right div.128").addClass("unselected");
	} else {
		jQuery("#price_agent_container div.right div.128").addClass("selected");
		jQuery("#price_agent_container div.right div.129").addClass("selected");
	}
};

function ap_price_agent_select_item_format(media_format) {
	jQuery("#price_agent_container div.right div." + media_format).removeClass("selected");
	jQuery("#price_agent_container div.right div." + media_format).removeClass("unselected");
	if (jQuery("#price_agent_media_format_" + media_format).is(':checked')) {
		jQuery("#price_agent_container div.right div." + media_format).removeClass("selected");
		jQuery("#price_agent_container div.right div." + media_format).addClass("unselected");
	} else {
		jQuery("#price_agent_container div.right div." + media_format).removeClass("unselected");
		jQuery("#price_agent_container div.right div." + media_format).addClass("selected");
	}
	jQuery("#price_agent_media_format_" + media_format).click();
};
