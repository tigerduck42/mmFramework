<?php
namespace mmFramework;

//ini_set('display_errors', 'on');
//ini_set('display_startup_errors', 'on');

error_reporting(E_ALL);
date_default_timezone_set("UTC");

if (!defined('DIR_BASE')) {
  define("DIR_BASE", realpath(dirname(__FILE__) . "/../.."));  // The project's base directory
}

$dirBase = realpath(dirname(__FILE__) . "/..");
define("DIR_FRAMEWORK", $dirBase);

require_once(DIR_FRAMEWORK . '/utils/fwUtilities.php');
require_once(DIR_FRAMEWORK . '/utils/utilities.php');

require_once(DIR_FRAMEWORK . '/thirdParty/vendor/autoload.php');

// Load classes
spl_autoload_register('mmFramework\customAutoLoader');

// Error handler
set_error_handler("mmFramework\customError");

// Exception handler
set_exception_handler("mmFramework\customException");

if (isset($_SERVER['DOCUMENT_ROOT']) && strlen($_SERVER['DOCUMENT_ROOT'])) {
  define("WEB_ROOT", $_SERVER['DOCUMENT_ROOT']);
} else {
  $filePath = realpath(dirname(__FILE__) . "/../../html/");
  define("WEB_ROOT", $filePath);
}

// Set correct timezone
$config = Config::getInstance();
date_default_timezone_set($config->timezone);

$template = new OutputRendererSmarty();
// Force asset loading
$template->forceAssetLoad = $config->forceAssetLoad;

$hostname = "Unknown host - " . HTTP::hostname();
$template->assign("hostname", $hostname);

$meCore = ltrim(HTTP::server('PHP_SELF'), "/");
$template->assign("meCore", $meCore);

$me = '/' . preg_replace("/\..+$/", "", $meCore);
if (in_array($me, array('/sitemap'))) {
  $me .= ".xml";
} else {
  $me .= ".html";
}

$template->assign("me", $me);
