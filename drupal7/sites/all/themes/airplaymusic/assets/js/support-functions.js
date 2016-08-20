function is_mobile() {    var agents = ['android', 'webos', 'iphone', 'ipad', 'blackberry'];var uagent = navigator.userAgent.toLowerCase();for (i in agents) {if (uagent.search(agents[i])>-1) {return true;}}return false;};function ap_frontpage_slide() {if (!is_mobile()){setTimeout(function(){ ap_frontpage_slide(); }, 10000);ap_frontpage_slide_hide();}};function ap_frontpage_slide_hide() {jQuery("#slide-item-user").toggle();jQuery("#slide-item-artist").toggle();};
function ap_frontpage_record_store_slide(id, frontpage_record_store_slide) {if (!frontpage_record_store_slide && frontpage_record_store_slidable) { frontpage_record_store_slide_id++; if (frontpage_record_store_slide_id > 3) { frontpage_record_store_slide_id = 1; } setTimeout(function () { ap_frontpage_record_store_slide(frontpage_record_store_slide_id, false); }, 4000); } else {frontpage_record_store_slidable = false;frontpage_record_store_slide_id = id;}jQuery("#frontpage_store_icons #slide1").hide();jQuery("#frontpage_store_icons #slide2").hide();jQuery("#frontpage_store_icons #slide3").hide();jQuery("#frontpage_store_icons #slide" + frontpage_record_store_slide_id).show();};

function ap_top_menu_click(element) {
	ap_top_menu_unselect_all_elements();
	ap_top_menu_select_element(element);
	
	if (jQuery("#dropdown_menu_element_" + element).is(":visible") === true) {
		ap_top_menu_show_element(element);
	} else {
		ap_top_menu_show_element(element);
		jQuery("#dropdown_menu_element_" + element).show();
	}
};

function ap_top_menu_show_element(id) {
	for (var i = 1; i <= 3; i++) { 
		if (jQuery("#dropdown_menu_element_" + i).length) {
			jQuery("#dropdown_menu_element_" + i).hide();
		}
	}
};

function ap_top_menu_select_element(id) { 

	jQuery("#top_menu_element_" + id).addClass("selected");
};

function ap_top_menu_unselect_all_elements() {
	for (var i = 1; i <= 3; i++) { 
		if (jQuery("#top_menu_element_" + i).length) {
			jQuery("#top_menu_element_" + i).removeClass("selected");
		}
	}
};