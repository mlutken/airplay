<?php
// $Id: ap_robotstxt.api.php,v 1.1.2.1 2009/02/19 22:08:00 hass Exp $


/**  Get Add additional lines to the site's robots.txt file.
 */
function hook_ap_robotstxt() {
  return array(
    'Disallow: /foo',
    'Disallow: /bar',
  );
}
