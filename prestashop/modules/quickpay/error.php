<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/../../init.php');

echo "<h1>".$this->l('Shopping cart payment')."</h1>";
echo "<p>".$this->l('An error has occurred in your payment, the order has not been validated')."</p>";

require_once(dirname(__FILE__).'/../../footer.php');

?>
