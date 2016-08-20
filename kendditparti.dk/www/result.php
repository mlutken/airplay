<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require_once ('../php_fix_include_path.php');
require_once ('www/string_utils.php');
require_once ('www/database_utils.php');
require_once ('www/Result__desktop.php');


// global $g_rawResult;

$saveid = $_GET['saveid'];
$pageData = getSurveyResults($saveid);
// $g_rawResult= getSurveyRawResults($saveid);
// 
$browser_type = getBrowserType();

$page = new Result__desktop($browser_type);
print $page->getHtmlForPage($pageData);
