<?
if ($history->id_order_state == _PS_OS_SHIPPING_) {
   $protocol = 6;
   $result = Db::getInstance()->getRow(' SELECT `id_cart` FROM '._DB_PREFIX_.'orders WHERE `id_order` = '.$id_order);
   $getamount = Db::getInstance()->getRow(' SELECT `total_paid` FROM '._DB_PREFIX_.'orders WHERE `id_order` = '.$id_order);
   $trans = Db::getInstance()->getRow(' SELECT `transaction` FROM '._DB_PREFIX_.'quickpay_transactions WHERE `id_cart` = '.$result['id_cart']);
   $merchant = Configuration::get('_QUICKPAY_MERCHANTID');
   $md5secret = Configuration::get('_QUICKPAY_MD5');

   $capamount = $getamount['total_paid'] * 100;
   $md5check = md5($protocol.'capture'.$merchant.$capamount.$trans['transaction'].$md5secret);
   $ch = curl_init();

   // set URL and other appropriate options
   curl_setopt($ch, CURLOPT_URL, "https://secure.quickpay.dk/api");
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   curl_setopt($ch, CURLOPT_HEADER, 0);
   curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
   curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
   curl_setopt($ch, CURLOPT_POST, 1);
   curl_setopt($ch, CURLOPT_POSTFIELDS, 'protocol='.$protocol.'&transaction='.$trans['transaction'].'&amount='.$capamount.'&protocol='.$protocol.'&msgtype=capture&merchant='.$merchant.'&md5check='.$md5check.'');


   // grab URL and pass it to the browser
   $data = curl_exec($ch);

   // close cURL resource, and free up system resourc
   curl_close($ch);
}
?>
