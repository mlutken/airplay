<?php

/*
 * $Date: 2014/05/25 05:47:40 $
 * Written by Kjeld Borch Egevang
 * E-mail: kjeld@mail4us.dk
 */

if (!defined('_PS_VERSION_'))
  exit;

class Quickpay extends PaymentModule
{
  private $_html = '';
  private $_postErrors = array();

  public function __construct()
  {
    $this->name = 'quickpay';
    $this->version = '3.25';
    $this->v14 = _PS_VERSION_ >= "1.4.0.0";
    $this->v15 = _PS_VERSION_ >= "1.5.0.0";
    $this->v16 = _PS_VERSION_ >= "1.6.0.0";
    if ($this->v14)
      $this->tab = 'payments_gateways';
    else
      $this->tab = 'Payment';
    $this->author = 'Kjeld Borch Egevang';

    /* The parent construct is required for translations */
    parent::__construct();
    // ini_set('display_errors', 1);
    // error_reporting(E_ALL);

    $this->page = basename(__FILE__, '.php');
    $this->displayName = $this->l('Quickpay');
    $this->description = $this->l('Accept payments by Quickpay');
    $this->confirmUninstall = $this->l('Are you sure you want to uninstall, and remove all information regarding this module?');
  }

  public function getSetup()
  {
    $this->setupVars = array(
      array('_QUICKPAY_MERCHANTID', 'merchantid', $this->l('Quickpay merchant ID'), '', ''),
      array('_QUICKPAY_MD5', 'md5', $this->l('Quickpay MD5 secret'), '', ''),
      array('_QUICKPAY_ORDER_PREFIX', 'orderprefix', $this->l('Order prefix'), '000', ''),
      array('_QUICKPAY_AUTOFEE', 'autofee', $this->l('Customer pays the card fee'), 0, ''),
      array('_QUICKPAY_USERNAME', 'username', $this->l('User name'), '', ''),
      array('_QUICKPAY_USERPASS', 'userpass', $this->l('User password'), '', ''),
      array('_QUICKPAY_AUTOCAPTURE', 'autocapture', $this->l('Auto-capture payments'), 0, ''),
      array('_QUICKPAY_TESTMODE', 'testmode', $this->l('Enable test mode'), 0, ''),
      array('_QUICKPAY_SHOWCARDS', 'showcards', $this->l('Show card logos on homepage'), 1, ''),
      array('_QUICKPAY_API', 'api', $this->l('Activate API'), 1, ''),
      array('_QUICKPAY_CREDITCARD', 'creditcard', $this->l('Creditcards combined window'), 0, ''),
      array('_QUICKPAY_VIABILL', 'viabill', $this->l('ViaBill - buy now, pay whenever you want'), 0, 'viabill'),
      array('_QUICKPAY_VIABILL_OVERLAY', 'viabilloverlay', $this->l('Show overlay with information about ViaBill'), 0, ''),
      array('_QUICKPAY_DK', 'dk', $this->l('Dankort'), 0, 'dankort'),
      array('_QUICKPAY_EDK', 'edk', $this->l('E-dankort'), 0, 'edankort'),
      array('_QUICKPAY_VISA', 'visa', $this->l('Visa card'), 0, 'visa,visa-dk'),
      array('_QUICKPAY_VELECTRON', 'visaelectron', $this->l('Visa Electron'), 0, 'visa-electron,visa-electron-dk'),
      array('_QUICKPAY_MASTERCARD', 'mastercard', $this->l('MasterCard'), 0, 'mastercard,mastercard-dk'),
      array('_QUICKPAY_MASTERCARDDEBET', 'mastercarddebet', $this->l('MasterCard Debet'), 0, 'mastercard-debet-dk'),
      array('_QUICKPAY_MAESTRO', 'maestro', $this->l('Maestro'), 0, '3d-maestro,3d-maestro-dk'),
      array('_QUICKPAY_A_EXPRESS', 'express', $this->l('American Express'), 0, 'american-express,american-express-dk'),
      array('_QUICKPAY_FORBRUGS_1886', 'f1886', $this->l('Forbrugsforeningen af 1886'), 0, 'fbg1886'),
      array('_QUICKPAY_DINERS', 'diners', $this->l('Diners Club'), 0, 'diners,diners-dk'),
      array('_QUICKPAY_LIC', 'lic', $this->l('LIC MasterCard'), 0, 'mastercard,mastercard-dk'),
      array('_QUICKPAY_LO', 'lo', $this->l('LO MasterCard'), 0, 'mastercard,mastercard-dk'),
      array('_QUICKPAY_JCB', 'jcb', $this->l('JCB'), 0, 'jcb'),
      array('_QUICKPAY_VISA_3D', 'visa_3d', $this->l('Visa card (3D)'), 0, '3d-visa,3d-visa-dk'),
      array('_QUICKPAY_VELECTRON_3D', 'visaelectron_3d', $this->l('Visa Electron (3D)'), 0, '3d-visa-electron,3d-visa-electron-dk'),
      array('_QUICKPAY_MASTERCARD_3D', 'mastercard_3d', $this->l('MasterCard (3D)'), 0, '3d-mastercard,3d-mastercard-dk'),
      array('_QUICKPAY_MASTERCARDDEBET_3D', 'mastercarddebet_3d', $this->l('MasterCard Debet (3D)'), 0, '3d-mastercard-debet-dk'),
      array('_QUICKPAY_MAESTRO_3D', 'maestro_3d', $this->l('Maestro (3D)'), 0, '3d-maestro,3d-maestro-dk'),
      array('_QUICKPAY_JCB_3D', 'jcb_3d', $this->l('JCB (3D)'), 0, '3d-jcb'),
      array('_QUICKPAY_PAYEX', 'payex', $this->l('PayEx'), 0, 'creditcard'),
      array('_QUICKPAY_DANSKE', 'danske', $this->l('Danske'), 0, 'danske-dk'),
      array('_QUICKPAY_NORDEA', 'nordea', $this->l('Nordea'), 0, 'nordea-dk'),
      array('_QUICKPAY_PAYPAL', 'paypal', $this->l('PayPal'), 0, 'paypal'),
      array('_QUICKPAY_PAII', 'paii', $this->l('Paii'), 0, 'paii'));
    $this->setup = new StdClass();
    $this->setup->lockNames = array();
    $this->setup->cardTypeLocks = array('creditcard');
    $this->setup->cardTypeLocks3d = array('3d-creditcard');
    $this->setup->cardTexts = array();
    $this->setup->creditCards = array();
    $this->setup->creditCards3d = array();
    $this->setup->creditCards2di = array();
    $this->setup->creditCards3di = array();
    $creditCards = array('dk', 'edk', 'visa', 'visaelectron', 'express', 'f1886', 'mastercard', 'mastercarddebet', 'maestro', 'lo', 'lic', 'diners', 'jcb');
    $creditCards2d = array(
      'visa_3d' => 'visa',
      'visaelectron_3d' => 'visaelectron',
      'mastercard_3d' => 'mastercard',
      'mastercarddebet_3d' => 'mastercarddebet',
      'maestro_3d' => 'maestro'
    );
    $creditCards3d = array(
      'visa_3d' => 'visa_3d',
      'visaelectron_3d' => 'visa_3d',
      'mastercard_3d' => 'mastercard_3d',
      'mastercarddebet_3d' => 'mastercard_3d',
      'maestro_3d' => 'mastercard_3d',
      'jcb_3d' => 'jcb_3d'
    );
    $setupVars = $this->sortSetup();
    $creditcardTypeLocks = array();
    foreach ($setupVars as $setupVar) {
      list($globName, $varName, $cardText, $defVal, $cardTypeLock) = $setupVar;
      $cardTypeLocks = explode(',', $cardTypeLock);
      $this->setup->cardTexts[$varName] = $cardText;
      if (in_array($varName, $creditCards)) {
	$this->setup->creditCards[$varName] = $cardText;
      }
      if (isset($creditCards3d[$varName])) {
	$this->setup->creditCards3d[$varName] = $cardText;
	if (isset($creditCards2d[$varName]))
	  $this->setup->creditCards2di[$creditCards2d[$varName]] = true;
	$this->setup->creditCards3di[$creditCards3d[$varName]] = true;
      }
      foreach ($cardTypeLocks as $name) {
	if ($varName == 'lo' || $varName == 'lic')
	  $this->setup->lockNames[$name] = 'mastercard';
	else
	  $this->setup->lockNames[$name] = $varName;
	$creditcardTypeLocks[] = $name;
      }
    }
    foreach ($this->setupVars as $setupVar) {
      list($globName, $varName, $cardText, $defVal, $cardTypeLock) = $setupVar;
      $cardTypeLocks = explode(',', $cardTypeLock);
      $this->setup->$varName = Configuration::get($globName);
      if (!$this->setup->$varName) {
	if (in_array($varName, $creditCards)) {
	  foreach ($cardTypeLocks as $name) {
	    if (!in_array($name, $creditcardTypeLocks) &&
	      !in_array('!'.$name, $this->setup->cardTypeLocks))
	    {
	      $this->setup->cardTypeLocks[] = '!'.$name;
	    }
	  }
	}
	if (isset($creditCards3d[$varName])) {
	  foreach ($cardTypeLocks as $name)
	    $this->setup->cardTypeLocks3d[] = '!'.$name;
	}
      }
    }
    // $this->dump($this->setup->cardTypeLocks);
    return $this->setup;
  }

  public function sortSetup()
  {
    $ordering = Configuration::get('_QUICKPAY_ORDERING');
    if ($ordering)
      $orderingList = explode(',', $ordering);
    else
      $orderingList = array();
    $setupDict = array();
    foreach ($this->setupVars as $setupVar) {
      list($globName, $varName, $cardText, $defVal, $cardTypeLock) = $setupVar;
      $setupDict[$varName] = $setupVar;
    }
    $setupVars = array();
    foreach ($orderingList as $varName) {
      $setupVars[] = $setupDict[$varName];
    }
    return $setupVars;
  }

  public function install()
  {
    $this->getSetup();
    if (!parent::install())
      return false;
    foreach ($this->setupVars as $setupVar) {
      list($globName, $varName, $cardText, $defVal, $cardTypeLock) = $setupVar;
      if (!Configuration::updateValue($globName, $defVal))
	return false;
    }
    if (!$this->registerHook('payment') ||
      !$this->registerHook('rightColumn') ||
      !$this->registerHook('adminOrder') ||
      !$this->registerHook('paymentReturn') ||
      !$this->registerHook('PDFInvoice'))
    {
      return false;
    }
    return Db::getInstance()->Execute('
      CREATE TABLE '._DB_PREFIX_.'quickpay_transactions (
	`id` int(6) NOT NULL AUTO_INCREMENT,
	`id_order` varchar(25),
    `id_cart` varchar(25),
    `transaction` int(10),
    `card_type` varchar(25),
    `currency` varchar(25),
    `amount` varchar(25),
    `qpstatmsg` varchar(25),
    `qpstat` varchar(25),
    `state` varchar(25),
    `time` varchar(25),
    `msgtype` varchar(25),
    `chstat` varchar(25),
    `chstatmsg` varchar(25),
    `merchant` varchar(25),
    `md5check` varchar(25),
    `credited` varchar(15),
    `deleted` VARCHAR(15),
    `cardnumber` VARCHAR(15),
    `amount_captured` VARCHAR(15),
    `amount_credited` VARCHAR(15),
    PRIMARY KEY(`id`))
    ENGINE=MyISAM default CHARSET=utf8');
  }

  public function uninstall()
  {
    $this->getSetup();
    if (!parent::uninstall())
      return false;
    foreach ($this->setupVars as $setupVar) {
      list($globName, $varName, $cardText, $defVal, $cardTypeLock) = $setupVar;
      if (!Configuration::deleteByName($globName))
	return false;
    }
    if (!Configuration::deleteByName('_QUICKPAY_ORDERING') ||
      !Configuration::deleteByName('_QUICKPAY_OVERLAY_CODE'))
      return false;
    return Db::getInstance()->Execute('DROP TABLE '._DB_PREFIX_.'quickpay_transactions');
  }

  public function dump($var, $name = NULL)
  {
    print "<pre>";
    if ($name)
      print "$name:\n";
    print_r($var);
    print "</pre>";
  }

  public function getContent()
  {
    $this->getSetup();
    $this->_html = '<h2>Quickpay</h2>';
    if (Tools::getValue('submitQuickpay'))
    {
      if (!Tools::getValue('merchantid'))
	$this->_postErrors[] = $this->l('Merchant ID is required.');
      if (!Tools::getValue('md5'))
	$this->_postErrors[] = $this->l('MD5 secret is required.');
      if (Tools::getValue('autofee') && $this->getFees(100) === false)
	$this->_postErrors[] = $this->l('User name/password not valid.');
      if (strlen(Tools::getValue('orderprefix')) != 3)
	$this->_postErrors[] = $this->l('Order prefix must be exactly 3 characters long.');

      if (!sizeof($this->_postErrors))
      {
	if ($this->v14) {
	  // Make sure .tpl files are updated
	  if (!Configuration::get('PS_FORCE_SMARTY_2')) {
	    global $smarty;
	    $smarty->clearCompiledTemplate('quickpay14.tpl');
	    $smarty->clearCompiledTemplate('quickpay.tpl');
	    $smarty->clearCompiledTemplate('rightquickpay.tpl');
	    $smarty->clearCompiledTemplate('complete.tpl');
	  }
	  Tools::clearCache($smarty);
	}
	$oldList = array();
	$newList = array();
	$ordering = Configuration::get('_QUICKPAY_ORDERING');
	if ($ordering)
	  $oldList = explode(',', $ordering);
	foreach ($this->setupVars as $setupVar) {
	  list($globName, $varName, $cardText, $defVal, $cardTypeLock) = $setupVar;
	  // $this->dump(Tools::getValue($varName), $varName);
	  if ($defVal == 1)
	    $value = Tools::getValue($varName, 0);
	  else
	    $value = Tools::getValue($varName);
	  Configuration::updateValue($globName, $value);
	  if ($cardTypeLock && $value)
	    $newList[] = $varName;
	}
	$value = Tools::getValue('overlayCode', '');
	Configuration::updateValue('_QUICKPAY_OVERLAY_CODE', $value, true);
	$orderingList = array();
	foreach ($oldList as $varName) {
	  if (in_array($varName, $newList))
	    $orderingList[] = $varName;
	}
	foreach ($newList as $varName) {
	  if (!in_array($varName, $orderingList))
	    $orderingList[] = $varName;
	}
	Configuration::updateValue('_QUICKPAY_ORDERING', implode(',', $orderingList));
	$this->getSetup();
	$this->displayConf();
      }
      else
	$this->displayErrors();
    }

    $this->displayQuickpay();
    $this->displayFormSettings();
    return $this->_html;
  }

  public function displayConf()
  {
    $this->_html .= '
      <div class="conf confirm">
      <img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />
      '.$this->l('Settings updated').'
      </div>';
  }

  public function displayErrors()
  {
    $nbErrors = sizeof($this->_postErrors);
    $this->_html .= '
      <div class="alert error">
      <h3>'.($nbErrors > 1 ? $this->l('There are') : $this->l('There is')).' '.$nbErrors.' '.($nbErrors > 1 ? $this->l('errors') : $this->l('error')).'</h3>
      <ol>';
    foreach ($this->_postErrors AS $error)
      $this->_html .= '<li>'.$error.'</li>';
    $this->_html .= '
      </ol>
      </div>';
  }


  public function displayQuickpay()
  {
    $this->_html .= '<img src="'.$this->_path.'img/quickpay.gif" style="float:left; margin-right:15px;"><b>'.
      $this->l('This module allows you to accept payments using Quickpay.').'</b><br /><br /><br /><br />';
  }

  public function displayFormSettings()
  {
    $setup = $this->setup;

    $this->_html .= '
      <form id="preview_import" action="'.$_SERVER['REQUEST_URI'].'" method="post">
      <fieldset>
      <legend><img src="../img/admin/contact.gif" />'.$this->l('Settings').'</legend>';
    $ordering = Configuration::get('_QUICKPAY_ORDERING');
    if ($this->v14 && !$this->v15) {
      // A bug in PS 1.4 destroys \r\n.
      Configuration::loadConfiguration();
    }
    $overlayCode = Configuration::get('_QUICKPAY_OVERLAY_CODE');
    if ($ordering)
      $orderingList = explode(',', $ordering);
    else
      $orderingList = array();
    $this->_html .= '<table><tr>';
    foreach ($orderingList as $varName) {
      $this->_html .= sprintf('<td align="center"><img src="%simg/%s.png" /></td>', $this->_path, $varName);
    }
    $this->_html .= '</tr><tr>';
    foreach ($orderingList as $varName) {
      $this->_html .= sprintf('<td align="center">&nbsp;%s&nbsp;</td>', $varName);
    }
    $this->_html .= '</tr></table>';
    $this->_html .= '</fieldset><fieldset>';
    foreach ($this->setupVars as $setupVar) {
      list($globName, $varName, $cardText, $defVal, $cardTypeLock) = $setupVar;
      // Overrule with POST data
      $setup->$varName = Tools::getValue($varName, $setup->$varName);
      $this->_html .= '';
      if ($varName == 'autofee')
	$this->_html .= '<div style="clear:both"><label>'.$cardText.'</label>
	<div class="margin-form"><input type="checkbox" name="autofee" id="autofee" value="1" '.($setup->$varName ? 'checked="checked"' : '').'/>';
      elseif ($varName == 'username')
	$this->_html .= '
	<b>'.$cardText.'</b> <input type="text" size="20" name="username" id="username" value="'.$setup->$varName.'" />';
      elseif ($varName == 'userpass')
	$this->_html .= '
	<b>'.$cardText.'</b> <input type="password" size="20" name="userpass" id="userpass" value="'.$setup->$varName.'" /></div></div>';
      elseif ($varName == 'orderprefix')
	$this->_html .= '<div style="clear:both"><label>'.$cardText.'</label>
	<div class="margin-form"><input type="text" size="5" name="'.$varName.'" value="'.$setup->$varName.'" /></div>';
      elseif ($defVal === '')
	$this->_html .= '<div style="clear:both"><label>'.$cardText.'</label>
	<div class="margin-form"><input type="text" size="33" name="'.$varName.'" value="'.$setup->$varName.'" /></div></div>';
      elseif ($varName == 'viabilloverlay') {
	$this->_html .= '<div style="clear:both"><label>'.$cardText.'</label>
	<div class="margin-form"><input style="float:left" type="checkbox" name="viabilloverlay" id="viabilloverlay" value="1" '.($setup->$varName ? 'checked="checked"' : '').'/>';
	$this->_html .= '<div id="overlaycode">&nbsp;<b>'.$this->l('Overlay code').'</b>&nbsp;<textarea name="overlayCode" style="vertical-align:top" rows="2" cols="120">'.$overlayCode.'</textarea></div></div></div>';
	// $this->_html .= '</div></div>';
      }
      else
	$this->_html .= '<div style="clear:both"><label>'.$cardText.'</label>
	<div class="margin-form"><input type="checkbox" name="'.$varName.'" value="1" '.($setup->$varName ? 'checked="checked"' : '').'/></div></div>';
      if ($varName == 'creditcard')
	$this->_html .= '<br />';
    }
    $this->_html .= '
      <script type="text/javascript">
	function quickpay_toggle()
	{
	  if ($("#autofee").attr("checked")) {
	    $("#username").removeAttr("disabled");
	    $("#username").css("background-color", "#FFFFFF");
	    $("#username").css("color", "#000000");
	    $("#userpass").removeAttr("disabled");
	    $("#userpass").css("background-color", "#FFFFFF");
	    $("#userpass").css("color", "#000000");
	  }
	  else {
	    $("#username").attr("disabled", "true");
	    $("#username").css("background-color", "#CCCCCC");
	    $("#username").css("color", "#AAAAAA");
	    $("#userpass").attr("disabled", "true");
	    $("#userpass").css("background-color", "#CCCCCC");
	    $("#userpass").css("color", "#AAAAAA");
	  }
	}
	function quickpay_otoggle()
	{
	  if ($("#viabilloverlay").attr("checked")) {
	    $("#overlaycode").show();
	    $("#viabilloverlay").css("float", "left");
	  }
	  else {
	    $("#overlaycode").hide();
	    $("#viabilloverlay").css("float", "none");
	  }
	}
	$(document).ready(function(){
	  quickpay_toggle();
	  quickpay_otoggle();
	});
	$("#autofee").click(function(event) {
	  quickpay_toggle();
	});
	$("#viabilloverlay").click(function(event) {
	  quickpay_otoggle();
	});
      </script>
      <br /><center><input type="submit" name="submitQuickpay" value="'.$this->l('Update settings').'" class="button" /></center>
      </fieldset>
      </form><br />';
    if (!$this->v14) {
      $this->_html .= '
	<fieldset class="width3">
	<legend>'.$this->l('Google Analytics').'</legend>'.
	$this->l('To use the module with google analytics e-tracking supply this regular match as a goal:').'<br />
	<b>'.$this->_path.'complete\.php*</b>
	</fieldset><br />';
    }
    $this->_html .= '
      <fieldset class="width3">
      <legend><img src="../img/admin/warning.gif" />'.$this->l('Information').'</legend>'.
      $this->l('In order for the module to work, you need to configure both merchant ID and MD5 secret.').'<br />'.
      $this->l('Both values can be found in the Quickpay control panel in the Settings tree.').'<br /><br />'.
      $this->l('If you let the customer pay the fees you must specify a valid login to Quickpay. The login is necessary to calculate the fees. For safety reasons it is recommended to create a login with minimal access rights for this purpose.').'<br /><br />'.
      $this->l('Notice that you may change the ordering of the cards. When you update the settings, newly enabled cards are always put in the end of the list.').'
      </fieldset>
      ';
  }

  public function hookPayment($params)
  {
    global $smarty, $cookie;

    $setup = $this->getSetup();
    $cart = $params['cart'];
    $invoiceAddress = new Address(intval($cart->id_address_invoice));
    $country = new Country($invoiceAddress->id_country);
    $deliveryAddress = new Address(intval($cart->id_address_delivery));
    $customer = new Customer(intval($cart->id_customer));
    $id_currency = intval($cart->id_currency);
    $currency = new Currency(intval($id_currency));

    $isoName = Language::getIsoById($cookie->id_lang);
    if ($isoName == 'da' || $isoName == 'dk')
      $language = 'da';
    else
      $language = $isoName;
    $cartTotal = number_format($cart->getOrderTotal(), 2, '', '');
    $taxTotal = $cart->getOrderTotal() - $cart->getOrderTotal(false);
    $okpage = 'http://'.$_SERVER['HTTP_HOST'].$this->_path.'complete.php?key='.$customer->secure_key.'&id_cart='.intval($cart->id).'&id_module='.intval($this->id);
    //$okpage = 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'order-confirmation.php?id_cart='.$cart->id.'&id_module='.$quickpay1->id.'&id_order='.$quickpay1->currentOrder.'&key='.$order->secure_key;
    if ($this->v16)
      $errorpage = 'http://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'index.php?controller=order&step=3';
    else
      $errorpage = 'http://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'order.php?step=3';
    $resultpage = 'http://'.$_SERVER['HTTP_HOST'].$this->_path.'validation.php';
    $msgtype = 'authorize';
    $protocol = '7';
    $splitpayment = '1';
    $description = '';
    $html = '';

    if (!Validate::isLoadedObject($invoiceAddress) ||
      !Validate::isLoadedObject($deliveryAddress) ||
      !Validate::isLoadedObject($customer))
      return 'Payment error: (invalid address or customer)';

    if ($setup->autofee)
      $fees = $this->getFees($cartTotal);
    else
      $fees = false;

    $trans = Db::getInstance()->getRow('SELECT *
      FROM '._DB_PREFIX_.'quickpay_transactions
      WHERE `id_cart` LIKE "___'.$cart->id.'.%"
      ORDER BY `id_cart` ASC');
    if ($trans) {
      $seq = explode('.', $trans['id_cart']);
      $seq[count($seq) - 1] += 1;
      $ordernumber = implode('.', $seq);
      Db::getInstance()->Execute('DELETE
	  FROM '._DB_PREFIX_.'quickpay_transactions
	  WHERE `id_cart` LIKE "___'.$cart->id.'.%"');
    }
    else {
      $ordernumber = $setup->orderprefix.intval($cart->id).'.0';
    }
    Db::getInstance()->Execute(
	'INSERT INTO '._DB_PREFIX_.'quickpay_transactions
	(`id_cart`) VALUES ("'.$ordernumber.'")');
    $smarty->assign(array(
      'invoiceAddress' => $invoiceAddress,
      'deliveryAddress' => $deliveryAddress,
      'customer' => $customer,
      'protocol' => $protocol,
      'msgtype' => $msgtype,
      'merchant' => $setup->merchantid,
      'language' => $language,
      'ordernumber' => $ordernumber,
      'currency' => $currency->iso_code,
      'okpage' => $okpage,
      'errorpage' => $errorpage,
      'resultpage' => $resultpage,
      'autocapture' => $setup->autocapture,
      'autofee' => $setup->autofee,
      'description' => $description,
      'testmode' => $setup->testmode,
      'splitpayment' => $splitpayment,
      'uri' => $_SERVER['REQUEST_URI'],
    ));

    $done = false;
    $done3d = false;
    $setupVars = $this->sortSetup();
    foreach ($setupVars as $setupVar) {
      list($globName, $varName, $cardText, $defVal, $cardTypeLock) = $setupVar;
      $cardList = array($varName);
      if (!$cardTypeLock || !$setup->$varName)
	continue;
      if ($setup->creditcard && isset($setup->creditCards[$varName])) {
	// Group these cards
	if ($done)
	  continue;
	// $cardText = implode(' / ', $setup->creditCards);
	$cardText = $this->l('credit card');
	$cardList = array_keys($setup->creditCards);
	$cardTypeLock = implode(',', $setup->cardTypeLocks);
	$done = true;
      }
      if ($setup->creditcard && isset($setup->creditCards3d[$varName])) {
	if ($done3d)
	  continue;
	$cardText = implode(' / ', $setup->creditCards3d);
	$cardList = 
	  array_merge(array_keys($setup->creditCards2di), array_keys($setup->creditCards3di));
	$cardTypeLock = implode(',', $setup->cardTypeLocks3d);
	$done3d = true;
      }
      if (!$setup->creditcard) {
	switch ($varName) {
	case 'visa_3d':
	case 'visaelectron_3d':
	  $cardList[] = 'visa_secure';
	  break;
	case 'mastercard_3d':
	case 'maestro_3d':
	case 'mastercarddebet_3d':
	  $cardList[] = 'mastercard_secure';
	  break;
	}
      }
      if ($varName == 'viabill') {
	// Autofee does not work
	$total = $cartTotal;
	$autofee = 0;
	if ($country->iso_code != 'DK')
	  continue;
      }
      else {
	$total = $cartTotal;
	$autofee = $setup->autofee;
      }
      $smarty->assign(array(
	'total' => $total,
	'autofee' => $autofee,
	'taxTotal' => $taxTotal,
	'shopName' => Configuration::get('PS_SHOP_NAME')
      ));
      $feeTxt = '<span style="display:table">';
      if ($cardList) {
	foreach ($cardList as $cardName) {
	  if (!empty($fees[$cardName])) {
	    $feeTxt .= '<span style="display:table-row">';
	    $feeTxt .= '<span style="display:table-cell">';
	    if ($cardName == 'viabill')
	      $feeTxt .= $this->l('Fee for').' '.$this->l('ViaBill').':&nbsp;';
	    else
	      $feeTxt .= $this->l('Fee for').' '.$setup->cardTexts[$cardName].':&nbsp;';
	    $feeTxt .= '</span>';
	    $feeTxt .= '<span style="display:table-cell">';
	    $feeTxt .= Tools::displayPrice($fees[$cardName] / 100, $currency);
	    if (!empty($fees[$cardName.'_f']))
	      $feeTxt .= ' ('.$this->l('foreign').': '.Tools::displayPrice($fees[$cardName.'_f'] / 100, $currency).')';
	    $feeTxt .= '</span>';
	    $feeTxt .= '</span>';
	    if ($cardName == 'dk') {
	      $feeTxt .= '<span style="display:table-row">';
	      $feeTxt .= '<span style="display:table-cell">';
	      $feeTxt .= $this->l('Fee for').' '.$this->l('Visadankort').':&nbsp;';
	      $feeTxt .= '</span>';
	      $feeTxt .= '<span style="display:table-cell">';
	      $feeTxt .= Tools::displayPrice($fees[$cardName] / 100, $currency);
	      if (!empty($fees[$cardName.'_f']))
		$feeTxt .= ' ('.$this->l('foreign').': '.Tools::displayPrice($fees[$cardName.'_f'] / 100, $currency).')';
	      $feeTxt .= '</span>';
	      $feeTxt .= '</span>';
	    }
	  }
	}
      }
      $feeTxt .= '</span>';
      $smarty->assign('fee', $feeTxt);
      //md5 calculation
      $md5check = md5(
	$protocol.
	$msgtype.
	$setup->merchantid.
	$language.
	$ordernumber.
	$total.
	$currency->iso_code.
	$okpage.
	$errorpage.
	$resultpage.
	$setup->autocapture.
	$autofee.
	$cardTypeLock.
	$description.
	$setup->testmode.
	$splitpayment.
	$setup->md5);
      $smarty->assign('cardtypelock', $cardTypeLock);
      $smarty->assign('imgs', $cardList);
      $smarty->assign('text', $this->l('Pay with').' '.$cardText);
      $smarty->assign('type', $varName);
      $smarty->assign('md5check', $md5check);
      if ($this->v16)
	$html .= $this->display(__FILE__, 'quickpay16.tpl');
      elseif ($this->v15)
	$html .= $this->display(__FILE__, 'quickpay.tpl');
      else
	$html .= $this->display(__FILE__, 'quickpay14.tpl');
    }

    return $html;
  }

  public function hookRightColumn($params)
  {
    global $smarty, $cookie;

    $overlayCode = Configuration::get('_QUICKPAY_OVERLAY_CODE');
    $overlayLines = explode("\n", $overlayCode);
    $overlayCode = '';
    foreach ($overlayLines as $line) {
      if ($line && strpos($line, '/jquery/') === false)
	$overlayCode .= $line."\n";
    }
    $setup = $this->getSetup();
    $setupVars = $this->sortSetup();
    $orderingList = array();
    $secureList = array();
    foreach ($setupVars as $setupVar) {
      list($globName, $varName, $cardText, $defVal, $cardTypeLock) = $setupVar;
      if ($cardTypeLock) {
	$smarty->assign($varName, $setup->$varName);
	switch ($varName) {
	case 'mastercarddebet':
	  $varName = 'mastercard';
	  break;
	case 'visa_3d':
	case 'visaelectron_3d':
	  if (!in_array('visa_3d', $secureList))
	    $secureList[] = 'visa_3d';
	  $varName = substr($varName, 0, -3);
	  break;
	case 'mastercard_3d':
	case 'maestro_3d':
	  if (!in_array('mastercard_3d', $secureList))
	    $secureList[] = 'mastercard_3d';
	  $varName = substr($varName, 0, -3);
	  break;
	case 'mastercarddebet_3d':
	  if (!in_array('mastercard_3d', $secureList))
	    $secureList[] = 'mastercard_3d';
	  $varName = 'mastercard';
	  break;
	default:
	  if ($secureList) {
	    foreach ($secureList as $secName)
	      if (!in_array($secName, $orderingList))
		$orderingList[] = $secName;
	  }
	  break;
	}
	if (!in_array($varName, $orderingList))
	  $orderingList[] = $varName;
      }
    }
    if ($secureList) {
      foreach ($secureList as $secName)
	if (!in_array($secName, $orderingList))
	  $orderingList[] = $secName;
    }
    $smarty->assign(array(
      'uri' => $_SERVER['REQUEST_URI'],
      'orderingList' => $orderingList,
      'viabilloverlay' => $setup->viabilloverlay,
      'showcards' => $setup->showcards,
      'overlaycode' => trim($overlayCode)
    ));;
    if ($setup->showcards || $setup->viabilloverlay)
      return $this->display(__FILE__, 'rightquickpay.tpl');
    else
      return '';
  }

  public function hookLeftColumn($params)
  {
    return $this->hookRightColumn($params);
  }

  public function hookPaymentReturn($params)
  {
    if (!$this->active)
      return;

    $order = $params['objOrder'];
    $trans = Db::getInstance()->getRow('SELECT *
      FROM '._DB_PREFIX_.'quickpay_transactions
      WHERE `id_cart` LIKE "___'.$order->id_cart.'.%"
      ORDER BY `id_cart` ASC');
    if ($trans) {
      $order->payment = $trans['card_type'];
      $order->update();
    }

    return $this->display(__FILE__, 'confirmation.tpl');
  }

  function hookAdminOrder($params)
  {
    if (Configuration::get('_QUICKPAY_API') == 1) {
      //Getting config vars
      $protocol = 7;
      $merchant = Configuration::get('_QUICKPAY_MERCHANTID');
      $md5secret = Configuration::get('_QUICKPAY_MD5');
      $orderprefix = Configuration::get('_QUICKPAY_ORDER_PREFIX');
      $order = new Order(intval($params['id_order']));
      $amountex = explode(".", $order->total_paid);
      $amount = $amountex[0].$amountex[1];
      $ordernumber = $order->id_cart;
      $trans = Db::getInstance()->getRow('SELECT `id_cart`, `transaction`
	  FROM '._DB_PREFIX_.'quickpay_transactions
	  WHERE `id_cart` LIKE "___'.$order->id_cart.'.%"
	  ORDER BY `id_cart` ASC');
      if ($trans)
	$ordernumber = $trans['id_cart'];
      else {
	// Compatibility
	$trans = Db::getInstance()->getRow('SELECT `id_cart`, `transaction`
	    FROM '._DB_PREFIX_.'quickpay_transactions
	    WHERE `id_cart` = '.$order->id_cart);
	if ($trans)
	  $ordernumber = $orderprefix.$trans['id_cart'];
      }
      $module = Db::getInstance()->getRow(' SELECT `module` FROM '._DB_PREFIX_.'orders WHERE `id_order` = '.intval($params['id_order']));
      if ($this->v16)
	$html = '<div class="col-lg-7"><div class="panel">
	  <h3><img src="'.$this->_path.'logo.gif" />
	  '.$this->l('Quickpay API').'</h3>';
      else
	$html = '<br />
	  <fieldset style="width: 400px">
	  <legend>'.$this->l('Quickpay API').'</legend><br />';
      if ($module['module'] == 'quickpay1' || $module['module'] == 'quickpay') {
	// Capture from post
	if (Tools::getValue('capture')) {
	  $postamount = Tools::getValue('acramount');
	  $final = Tools::getValue('final');
	  $capamount = Tools::getValue('acramount') * 100;
	  //$html .= Tools::getValue('acramount');
	  //$html .= $capamount;
	  $md5check = md5($protocol.'capture'.$merchant.$capamount.$final.$trans['transaction'].$md5secret);
	  $ch = curl_init();

	  // set URL and other appropriate options
	  curl_setopt($ch, CURLOPT_URL, "https://secure.quickpay.dk/api");
	  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	  curl_setopt($ch, CURLOPT_HEADER, 0);
	  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	  curl_setopt($ch, CURLOPT_POST, 1);
	  curl_setopt($ch, CURLOPT_POSTFIELDS, 'protocol='.$protocol.'&finalize='.$final.'&transaction='.$trans['transaction'].'&amount='.$capamount.'&protocol='.$protocol.'&msgtype=capture&merchant='.$merchant.'&md5check='.$md5check.'');


	  // grab URL and pass it to the browser
	  $data = curl_exec($ch);

	  // close cURL resource, and free up system resourc
	  curl_close($ch);
	  $xml = new SimpleXmlElement($data);
	  if ($xml->qpstat == '000') {
	    $html .= '<b>'.$this->l('Capture successful.').'</b><br /><br />';
	  }

	  if($xml->qpstat != '000' && $xml->qpstat) {
	    $html .= '<b>'.$this->l('Capture failed with error code:').' '.$xml->qpstat.' '.$xml->qpstatmsg.'</b><br /><br />';
	  }
	}
	if (Tools::getValue('refund')) {
	  $refamount = Tools::getValue('acramount') * 100;
	  $md5check = md5($protocol.'refund'.$merchant.$refamount.$trans['transaction'].$md5secret);
	  $ch = curl_init();

	  // set URL and other appropriate options
	  curl_setopt($ch, CURLOPT_URL, "https://secure.quickpay.dk/api");
	  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	  curl_setopt($ch, CURLOPT_HEADER, 0);
	  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	  curl_setopt($ch, CURLOPT_POST, 1);
	  curl_setopt($ch, CURLOPT_POSTFIELDS, 'protocol='.$protocol.'&transaction='.$trans['transaction'].'&amount='.$refamount.'&protocol='.$protocol.'&msgtype=refund&merchant='.$merchant.'&md5check='.$md5check.'');


	  // grab URL and pass it to the browser
	  $data = curl_exec($ch);

	  // close cURL resource, and free up system resourc
	  curl_close($ch);
	  $xml = new SimpleXmlElement($data);
	  if ($xml->qpstat == '000') {
	    $html .= '<b>'.$this->l('Action: Refunded!').'</b><br /><br />';
	  }

	  if($xml->qpstat != '000' && $xml->qpstat) {
	    $html .= '<b>'.$this->l('Refund failed with error code:').' '.$xml->qpstat.' '.$xml->qpstatmsg.'</b><br /><br />';
	  }
	}
	if (Tools::getValue('cancel')) {
	  $refamount = Tools::getValue('acramount') * 100;
	  $md5check = md5($protocol.'cancel'.$merchant.$refamount.$trans['transaction'].$md5secret);
	  $ch = curl_init();

	  // set URL and other appropriate options
	  curl_setopt($ch, CURLOPT_URL, "https://secure.quickpay.dk/api");
	  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	  curl_setopt($ch, CURLOPT_HEADER, 0);
	  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	  curl_setopt($ch, CURLOPT_POST, 1);
	  curl_setopt($ch, CURLOPT_POSTFIELDS, 'protocol='.$protocol.'&transaction='.$trans['transaction'].'&amount='.$refamount.'&protocol='.$protocol.'&msgtype=cancel&merchant='.$merchant.'&md5check='.$md5check.'');


	  // grab URL and pass it to the browser
	  $data = curl_exec($ch);

	  // close cURL resource, and free up system resourc
	  curl_close($ch);
	  $xml = new SimpleXmlElement($data);

	  //Bugfinder
	  //$html .=''.print_r($xml).'';

	  //Checking state
	  if ($xml->qpstat == '000') {
	    $html .= '<b>'.$this->l('Action: Cancelled!').'</b><br /><br />';
	  }

	  if($xml->qpstat != '000' && $xml->qpstat) {
	    $html .= '<b>'.$this->l('Refund failed with error code:').' '.$xml->qpstat.' '.$xml->qpstatmsg.'</b><br /><br />';
	  }
	}
	$html .= '
	  '.$this->l('Quickpay order ID (cart ID):').' <b>'.$ordernumber.'</b><br />
							'.$this->l('Transaction id:').' <b>'.$trans['transaction'].'</b><br />
												  '.$this->l('State:').'
															';
	//Getting status reply from quickpay

	$md5check = md5($protocol.'status'.$merchant.$ordernumber.$md5secret);
	$ch = curl_init();

	// set URL and other appropriate options
	curl_setopt($ch, CURLOPT_URL, "https://secure.quickpay.dk/api");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, 'protocol='.$protocol.'&ordernumber='.$ordernumber.'&protocol='.$protocol.'&msgtype=status&merchant='.$merchant.'&md5check='.$md5check.'');


	// grab URL and pass it to the browser
	$data = curl_exec($ch);

	// close cURL resource, and free up system resourc
	curl_close($ch);
	$xml = new SimpleXmlElement($data);

	//Bugfinder
	// $html .=''.print_r($xml).'';

	//Displaying No IP supplied error
	if ($xml->qpstat == '008') {
	  if ($xml->qpstatmsg == 'Transaction not found') {
	    $md5check = md5($protocol.'status'.$merchant.$ordernumber.$md5secret);
	    $ch = curl_init();

	    // set URL and other appropriate options
	    curl_setopt($ch, CURLOPT_URL, "https://secure.quickpay.dk/api");
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, 'protocol='.$protocol.'&ordernumber='.$ordernumber.'&protocol='.$protocol.'&msgtype=status&merchant='.$merchant.'&md5check='.$md5check.'');


	    // grab URL and pass it to the browser
	    $data = curl_exec($ch);

	    // close cURL resource, and free up system resourc
	    curl_close($ch);
	    $xml = new SimpleXmlElement($data);

	    //Bugfinder
	    //$html .=''.print_r($xml).'';

	  }
	  if ($xml->qpstat == '008') {
	    $html .= '<br />Quickpay error msg: '.$xml->qpstatmsg;
	    if ($this->v16)
	      $html .= '</div></div>';
	    else
	      $html .= '</fieldset>';
	    return $html;
	  }
	}
	if ($trans['transaction'] == NULL) {
	  Db::getInstance()->Execute(
	      'UPDATE `'._DB_PREFIX_.'quickpay_transactions`
	      SET `transaction` = "'.$xml->transaction.'"
	      WHERE `id_cart` = "'.$ordernumber.'"');
	}

	//finding state and making history
	$loop = 0;
	// $html .='<pre>'.print_r($xml, true).'</pre>';
	while ($xml->history[$loop]) {
	  $currentamount1 =
	    number_format($xml->history[$loop]->amount / 100, 2);
	  $currentamount2 = explode(",", $currentamount1);
	  $get = 0;
	  $currentamount = '';
	  while (isset($currentamount2[$get])) {
	    $currentamount = $currentamount.$currentamount2[$get];
	    $get++;
	  }
	  if ($xml->history[$loop]->msgtype == 'authorize') {
	    $msg = '<b>'.$this->l('Authorized with amount: ').''.
	      self::displayPrice($currentamount, $xml->currency).'</b>';
	    $acrhamount = $currentamount;
	    $stat = 1;
	    if ($xml->history[$loop]->qpstat != '000')
	      $msg = '<b>'.$this->l('Payment error with code:').' '.
		$xml->history[$loop]->qpstat.'</b>';
	  }
	  if ($xml->history[$loop]->msgtype == 'capture') {
	    $msg = '<b>'.$this->l('Captured with amount: ').''.
	      self::displayPrice($currentamount, $xml->currency).'</b>';
	    $acrhamount = $currentamount;
	    $stat = 2;
	    if ($xml->history[$loop]->qpstat != '000') {
	      $msg = '<b>'.$this->l('Capture error with code:').' '.
		$xml->history[$loop]->qpstat.'</b>';
	      $stat = 1;
	    }
	  }
	  if ($xml->history[$loop]->msgtype == 'refund') {
	    $msg = '<b>'.$this->l('Refunded with amount: ').''.
	      self::displayPrice($currentamount, $xml->currency).'</b>';
	    $acrhamount = $currentamount;
	    $stat = 3;
	    if ($xml->history[$loop]->qpstat != '000') {
	      $msg = '<b>'.$this->l('Refund error with code:').' '.
		$xml->history[$loop]->qpstat.'</b>';
	      $stat = 2;
	    }
	  }
	  if ($xml->history[$loop]->msgtype == 'cancel') {
	    $msg = '<b>'.$this->l('Transaction cancelled ').'</b>';
	    $stat = 4;
	    if ($xml->history[$loop]->qpstat != '000')
	      $msg = '<b>'.$this->l('Cancel error with code:').' '.
		$xml->history[$loop]->qpstat.'</b>';
	  }
	  $loop++;
	}
	$msg .= '<br />'.$this->l('Card type:').'<b> '. $xml->cardtype .'</b><br />';
	$splitpayment = $xml->splitpayment;
	$msg .= $this->l('Split payment:').'<b> '.($splitpayment == 1 || $splitpayment == 2 ? $this->l('Yes') : $this->l('No (Change in Quickpay manager)')).'</b><br />';
	$msg .= '<br /><br />'.$this->l('The history of this transaction:').'<br /><table width="100%"><tr><th>'.$this->l('Date:').'</th><th>'.$this->l('Action:').'</th><th>'.$this->l('Amount:').'</th></tr>';
	$loop = 0;
	$capted = 0;
	$refundmade = 0;
	$cancelmade = 0;
	while ($xml->history[$loop]) {
	  $currentamount1 =
	    number_format($xml->history[$loop]->amount / 100, 2);
	  $currentamount2 = explode(",", $currentamount1);
	  $get = 0;
	  $currentamount = '';
	  while (isset($currentamount2[$get])) {
	    $currentamount = $currentamount.$currentamount2[$get];
	    $get++;
	  }

	  $time = strtotime($xml->history[$loop]->time);
	  $timestamp = date('Y-m-d H:i:s', $time);
	  $timestamp = str_replace(' ', '&nbsp;', $timestamp);
	  if ($xml->history[$loop]->msgtype == 'authorize') {
	    $msg .= '<tr><td>'.$timestamp.'</td><td>'.$this->
	      l('Authorized with amount: ').'</td><td>'.
	      self::displayPrice($currentamount, $xml->currency).'</td></tr>';
	    $currentauth = $currentamount;
	  }
	  if ($xml->history[$loop]->msgtype == 'capture') {
	    $msg .= '<tr><td>'.$timestamp.'</td><td>';
	    if ($xml->history[$loop]->qpstat != '000') {
	      $msg .= '<b>'.$this->l('Capture error with code:').
		' '.$xml->history[$loop]->qpstat.
		'</b></td></tr>';
	    }
	    else {
	      $msg .= $this->l('Captured with amount: ').
		'</td><td>'.self::displayPrice($currentamount, $xml->currency).
		'</td></tr>';
	      $capted = $capted + $currentamount;
	    }
	  }
	  if ($xml->history[$loop]->msgtype == 'refund') {
	    $msg .= '<tr><td>'.$timestamp.'</td><td>';
	    if ($xml->history[$loop]->qpstat != '000') {
	      $msg .= '<b>'.$this->l('Refund error with code:').' '.
		$xml->history[$loop]->qpstat.'</b></td></tr>';
	    }
	    else {
	      $refundmade = 1;
	      $msg .= $this->l('Refunded with amount: ').
		'</td><td>'.self::displayPrice($currentamount, $xml->currency).
		'</td></tr>';
	    }
	  }
	  if ($xml->history[$loop]->msgtype == 'cancel') {
	    $msg .= '<tr><td>'.$timestamp.'</td><td>';
	    if ($xml->history[$loop]->qpstat != '000') {
	      $msg = '<b>'.$this->l('Cancel error with code:').' '.
		$xml->history[$loop]->qpstat.'</b></td></tr>';
	    }
	    else {
	      $msg .= $this->l('Transaction cancelled ').
		'</td><td>'.self::displayPrice($currentamount, $xml->currency).
		'</td></tr>';
	      $cancelmade = 1;
	    }
	  }
	  $loop++;
	}
	$splitpayment = $xml->splitpayment;
	$msg .= '</table>';
	$html .= ''.$msg.'';
	$html .= '<table>';
	$html .= '<tr><th style="white-space: nowrap">Fraud risk:</th>';
	$html .= '<td>'.$xml->fraudprobability[0].'</td></tr>';
	$html .= '<tr><th style="white-space: nowrap" valign="top">Fraud remarks:</th>';
	$html .= '<td>'.$xml->fraudremarks[0].'</td></tr>';
	$html .= '</table>';

	if (!$refundmade && !$cancelmade) {
	  $html .='<br /><br />'.$this->l('Do NOT use 1000 separators like 1,000.00. Use only 1000.00').'';
	  $resttocap1 = $currentauth - $capted;
	  $resttocap1 = number_format($resttocap1, 2);
	  $resttocap2 = explode(",", $resttocap1);
	  $resttocap = '';
	  $uhm = 0;
	  while (isset($resttocap2[$uhm])) {
	    $resttocap = $resttocap.$resttocap2[$uhm];
	    $uhm++;
	  }
	  $confirm = "";
	  if ($this->v15)
	    $url = 'index.php?controller='.Tools::getValue('controller');
	  else
	    $url = 'index.php?tab='.Tools::getValue('tab');
	  $html .='<br /><br />
	    <form action="'.$url.'&id_order='.Tools::getValue('id_order').'&vieworder&token='.Tools::getValue('token').'" method="post" name="capture-cancel">
	    <table>
	    <tr><th style="white-space: nowrap">'.$this->l('Amount to capture: ').'</th>
	    <td><input type="text" size="10" name="acramount" value="'.($splitpayment == 2 ? 'Closed (Finalized)' : $resttocap).'"/>
	    <input type="submit" name="capture" value="'.$this->l('Capture').'" class="button" onclick="return confirm(\''.$this->l('Are you sure you want to capture the amount?').'\')"/></td></tr>
	    <tr><th valign="top">'.$this->l('Finalize: ').'
	    </th><td>
	    <input type="checkbox" name=final value=1>
	    </td></tr>
	    <tr><td colspan="2">
	    ('.$this->l('This will finalize the remaining amount. To finalize without further capturing, enter 0.00 at the capture field and check the box').')
	    </td></tr></table>';
	  if (!$capted) {
	    $html .='<br /><center><input type="submit" name="cancel" value="'.$this->l('Cancel the transaction!').'" class="button" onclick="return confirm(\''.$this->l('Are you sure you want cancel the transaction?').'\')"/></center>';
	  }
	  $html .='</form>';

	  // captured show refund
	  if ($stat == 2) {
	    $html .='<br /><form action="'.$url.'&id_order='.Tools::getValue('id_order').'&vieworder&token='.Tools::getValue('token').'" method="post" name="refund" style="text-align:center;">
	      <table>
	      <tr><th style="white-space: nowrap">
	      '.$this->l('Amount to refund: ').'</th>
	      <td><input type="text" size="10" name="acramount" id="acramountref" value="" />
	      <input type="submit" name="refund" value="'.$this->l('Refund').'" class="button" onclick="return confirm(\''.$this->l('Are you sure you want to refund the amount?').'\');"/></td>
	      </tr></table>
	      </form>';
	  }
	}
	else {
	  $html .='<br /><b>'.$this->l('A refund or cancel has been made, transaction closed.').'<br /></b>';}
      }
      else {
	$html .= ''.$this->l('No transactions for this order.').'<br />'; 
      }
      $html .= '<br /><a href="https://manager.quickpay.net" target="_blank" style="color: blue;">'.$this->l('Quickpay manager').'</a>';
      if ($this->v16)
	$html .= '</div></div>';
      else
	$html .= '</fieldset>';
      return $html;
    }
  }

  public function displayPrice($amount, $isoCurrency)
  {
    $currency = intval(Currency::getIdByIsoCode($isoCurrency));
    if (!$currency)
      $currency = NULL;
    return Tools::displayPrice($amount, $currency, false, NULL);
  }

  public function addFee(&$cart, $fee)
  {
    $defLang = intval(Configuration::get('PS_LANG_DEFAULT'));
    $txt = $this->l('Credit card fee', $this->name, $defLang);
    $row = Db::getInstance()->getRow('SELECT `id_product`
	FROM '._DB_PREFIX_.'product
	LEFT JOIN '._DB_PREFIX_.'product_lang
	USING (`id_product`)
	WHERE `name` = "'.$txt.'"');
    if ($row)
      $product = new Product($row['id_product']);
    else
      $product = new Product();
    $product->name = array($defLang => $txt);
    $product->price = $fee / 100;
    $product->quantity = 100;
    $product->link_rewrite = array($defLang => 'fee');
    $product->reference = $this->l('cardfee');
    if ($this->v14)
      $product->id_tax_rules_group = 0;
    else
      $product->id_tax = 0;
    // $product->id_currency = 1;
    if ($row)
      $product->update();
    else
      $product->add();
    if ($this->v15)
      StockAvailable::setQuantity($product->id, 0, 100);
    $cart->deleteProduct($product->id);
    $res = $cart->updateQty(1, $product->id);
  }

  public function hookPDFInvoice($params)
  {
    if ($this->v15) {
      $object = $params['object'];
      $order = new Order(intval($object->id_order));
    }
    else {
      $pdf = $params['pdf'];
      $order = new Order($params['id_order']) ;
    }
    $trans = Db::getInstance()->getRow('SELECT *
      FROM '._DB_PREFIX_.'quickpay_transactions
      WHERE `id_cart` LIKE "___'.$order->id_cart.'.%"
      OR `id_cart` = '.$order->id_cart.'
      ORDER BY `id_cart` ASC');
    if (isset($trans['transaction'])) {
      if ($this->v15) {
	$html = '<table><tr>';
	$html .= '<td style="width:6%">&nbsp;</td>';
	$html.= '<td style="width:94%">TransID: '.$trans['transaction'].'</td>';
	$html .= '</tr></table>';
	if ($trans['card_type'] == 'ViaBill') {
	  $html .= '<br/>';
	  $html .= 'Det skyldige beløb kan alene betales med frigørende virkning til ViaBill, som fremsender særskilt opkrævning.';
	  $html .= '<br/>';
	  $html .= 'Betaling kan ikke ske ved modregning af krav, der udspringer af andre retsforhold.';
	}
	return $html;
      }
      else {
	if ($this->v14)
	  $encoding = $pdf->encoding();
	else
	  $encoding = 'iso-8859-1';
	$oldStr = Tools::iconv('utf-8', $encoding, $order->payment);
	$newStr = Tools::iconv('utf-8', $encoding,
	  $order->payment.' TransID: '.$trans['transaction']);
	$pdf->pages[1] = str_replace($oldStr, $newStr, $pdf->pages[1]);
	if ($trans['card_type'] == 'ViaBill') {
	  $pdf->Ln(14);
	  $width = 165;
	  $txt = Tools::iconv('utf-8', $encoding,
	    'Det skyldige beløb kan alene betales med frigørende virkning til ViaBill, som fremsender særskilt opkrævning.');
	  $pdf->Cell($width, 3, $txt, 0, 2, 'L');
	  $txt = Tools::iconv('utf-8', $encoding,
	    'Betaling kan ikke ske ved modregning af krav, der udspringer af andre retsforhold.');
	  $pdf->Cell($width, 3, $txt, 0, 2, 'L');
	}
      }
    }
  }

  public function getFees($amount)
  {
    $setup = $this->setup;
    $username = Tools::getValue('username', $setup->username);
    $userpass = Tools::getValue('userpass', $setup->userpass);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.quickpay.net/acquirers/nets/fees/".$amount);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_USERPWD, $username.':'.$userpass);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-QuickPay-Client-API-Version: 0.1'));
    $data = curl_exec($ch);
    curl_close($ch);
    $fees = array();
    if ($data) {
      $rows = json_decode($data);
      if (count($rows)) {
	foreach ($rows as $row) {
	  if (isset($setup->lockNames[$row->lockname])) {
	    $lockName = $setup->lockNames[$row->lockname]; 
	    if (isset($fees[$lockName])) {
	      if ($row->fee < $fees[$lockName]) {
		$fees[$lockName.'_f'] = $fees[$lockName];
		$fees[$lockName.'_3d_f'] = $fees[$lockName.'_3d'];
		$fees[$lockName] = $row->fee;
		$fees[$lockName.'_3d'] = $row->fee;
	      }
	      if ($row->fee > $fees[$lockName]) {
		$fees[$lockName.'_f'] = $row->fee;
		$fees[$lockName.'_3d_f'] = $row->fee;
	      }
	    }
	    else {
	      $fees[$lockName] = $row->fee;
	      $fees[$lockName.'_3d'] = $row->fee;
	    }
	  }
	}
	return $fees;
      }
    }
    return false;
  }
}

?>
