<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
require_once(dirname(__FILE__).'/quickpay.php');


$msgtype= Tools::getValue('msgtype');
$ordernumber= Tools::getValue('ordernumber');
$amount= Tools::getValue('amount');
$currency= Tools::getValue('currency');
$time= Tools::getValue('time');
$state= Tools::getValue('state');
$qpstat= Tools::getValue('qpstat');
$qpstatmsg= Tools::getValue('qpstatmsg');
$chstat= Tools::getValue('chstat');
$chstatmsg= Tools::getValue('chstatmsg');
$merchant= Tools::getValue('merchant');
$merchantemail= Tools::getValue('merchantemail');
$transaction= Tools::getValue('transaction');
$cardtype= Tools::getValue('cardtype');
$cardnumber= Tools::getValue('cardnumber');
$cardhash= Tools::getValue('cardhash');
$splitpayment = Tools::getValue('splitpayment');
$acquirer = Tools::getValue('acquirer');
$fraudprobability = Tools::getValue('fraudprobability');
$fraudremarks = Tools::getValue('fraudremarks');
$fraudreport = Tools::getValue('fraudreport');
$fee = Tools::getValue('fee');
$md5checkget = Tools::getValue('md5check');

$quickpay = new Quickpay();
$id_cart = intval(substr($ordernumber, 3));
$cart = new Cart($id_cart);
if ($quickpay->v15)
  Shop::setContext(Shop::CONTEXT_SHOP, $cart->id_shop);

$md5 = Configuration::get('_QUICKPAY_MD5');
$autofee = Configuration::get('_QUICKPAY_AUTOFEE');

$md5check = md5($msgtype.$ordernumber.$amount.$currency.$time.$state.$qpstat.$qpstatmsg.$chstat.$chstatmsg.$merchant.$merchantemail.$transaction.$cardtype.$cardnumber.$cardhash.$acquirer.$splitpayment.$fraudprobability.$fraudremarks.$fraudreport.$fee.$md5);
$dbTable = _DB_PREFIX_.'quickpay_transactions';

/*
echo "$md5check\n";
echo $cart->secure_key;
echo 'og';
if ($cart->OrderExists()) {
  print "delete order\n";
  $id_order = Order::getOrderByCartId($id_cart);
  $order = new Order($id_order);
  $order->delete();
}
*/
if ($cart->OrderExists() == 0) {
  //echo 'og';
  if ($md5checkget == $md5check) {
    if ($qpstat == '000' && $state != 5) {
      $amount1 = number_format($amount / 100, 2, ".", "");
      $query = 'DELETE FROM '.$dbTable.' WHERE id_cart LIKE "___'.$cart->id.'.%"';
      Db::getInstance()->Execute($query);
      $query = "INSERT INTO $dbTable (md5check, cardnumber, card_type, msgtype, id_cart, amount, currency, time, state, qpstat, qpstatmsg, chstat, chstatmsg, merchant, transaction)
	VALUES ('$md5checkget', '$cardnumber', '$cardtype', '$msgtype', '$ordernumber', '$amount','$currency', '$time', '$state', '$qpstat', '$qpstatmsg', '$chstat', '$chstatmsg', '$merchant', '$transaction')";
      Db::getInstance()->Execute($query);
      $extra_vars = array('transaction_id' => $transaction, 'cardtype' => $cardtype);
      if ($fee > 0)
	$quickpay->addFee($cart, $fee);
      if ($quickpay->v14) {
	if ($quickpay->validateOrder($cart->id, _PS_OS_PAYMENT_, $amount1, $quickpay->displayName, NULL, $extra_vars, NULL, false, $cart->secure_key))
	{
	  $orderId = Order::getOrderByCartId($cart->id);
	}
	else {
	  echo "Prestashop error - unable to process order..";
	}
      }
      else {
	if ($quickpay->validateOrder($cart->id, _PS_OS_PAYMENT_, $amount1, $cardtype, NULL, $extra_vars, NULL, false))
	{
	  $orderId = Order::getOrderByCartId($cart->id);
	}
	else {
	  echo "Prestashop error - unable to process order..";
	}
      }
    }
  }
}

?>
