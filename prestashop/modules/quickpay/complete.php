<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');

$id_cart = Tools::getValue('id_cart');
$id_module = Tools::getValue('id_module');
$key = Tools::getValue('key');
$id_order = Order::getOrderByCartId(intval($id_cart));
if (!$id_order || !$id_module || !$key)
  Tools::redirect('history.php');
$order = new Order(intval($id_order));
if (!Validate::isLoadedObject($order) || $order->id_customer != $cookie->id_customer)
  Tools::redirect('history.php');

if (_PS_VERSION_ >= "1.5.0.0")
  Tools::redirect('index.php?controller=order-confirmation&id_cart='.$id_cart.
  '&id_module='.$id_module.'&id_order='.$id_order.'&key='.$key);
elseif (_PS_VERSION_ >= "1.4.0.0")
  Tools::redirect('order-confirmation.php?id_cart='.$id_cart.
  '&id_module='.$id_module.'&id_order='.$id_order.'&key='.$key);
else {
  $smarty->assign(array(
    'order' => $id_order,
    'HOOK_ORDER_CONFIRMATION' => Hook::orderConfirmation(intval($id_order)),
  ));

  $smarty->display(dirname(__FILE__).'/complete.tpl');

  include(dirname(__FILE__).'/../../footer.php');
}

?>
