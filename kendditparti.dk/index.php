<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require_once ('php_fix_include_path.php');
require_once ('www/string_utils.php');
require_once ('www/database_utils.php');
require_once ('www/MainPage__desktop.php');


$pageData = [];
$browser_type = getBrowserType();

$page = new MainPage__desktop($browser_type);
print $page->getHtmlForPage($pageData);