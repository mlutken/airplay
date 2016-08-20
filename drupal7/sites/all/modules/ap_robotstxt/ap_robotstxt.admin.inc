<?php
// $Id: ap_robotstxt.admin.inc,v 1.1.2.2 2009/02/19 22:49:06 hass Exp $


/**
 * Administration settings form.
 *
 * @see system_settings_form()
 */
function ap_robotstxt_admin_settings() {
    $topDomain_s = ap_site_top_domain();
    $var = "ap_robotstxt_{$topDomain_s}";

    $form['ap_robotstxt'] = array(
        '#type' => 'textarea',
        '#title' => t("Common contents of robots.txt for all Airplay Music's sites"),
        '#default_value' => _ap_robotstxt_get_content(),
        '#cols' => 60,
        '#rows' => 20,
        '#wysiwyg' => FALSE,
    );

    
    $form[$var] = array(
        '#type' => 'textarea',
        '#title' => t("Additional domain specific contents of robots.txt for airplay top domain: ") . ap_site_top_domain(),
        '#default_value' => _ap_robotstxt_get_content_site(),
        '#cols' => 60,
        '#rows' => 20,
        '#wysiwyg' => FALSE,
    );

    return system_settings_form($form);
}
