<?php
namespace mmFramework;
require_once(DIR_FRAMEWORK . '/utils/fwUtilities.php');
require_once(DIR_FRAMEWORK . '/utils/utilities.php');


error_reporting(E_ALL | E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
set_error_handler("mmFramework\customError");

if(isset($_SERVER['DOCUMENT_ROOT']) && strlen($_SERVER['DOCUMENT_ROOT'])) {
	define("WEB_ROOT", $_SERVER['DOCUMENT_ROOT']);
}
else {
	$filePath = realpath(dirname(__FILE__) . "/../../html/");
	define("WEB_ROOT", $filePath);
}

// Load classes
spl_autoload_register('mmFramework\customAutoLoader');

// Set correct timezone
$config = Config::getInstance();
date_default_timezone_set($config->timezone);

$template = new OutputRendererSmarty();

$hostname = "Unknown host - " . HTTP::hostname();
$template->assign("hostname", $hostname);

$me = ltrim(HTTP::server('PHP_SELF'), "/");
$me = '/' . preg_replace("/\..+$/","",$me);
if(in_array($me, array('/sitemap')))
	$me .= ".xml";
else
	$me .= ".html";

$template->assign("me", $me);


?>