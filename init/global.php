<?php
error_reporting(E_ALL | E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
set_error_handler("customError");

if(isset($_SERVER['DOCUMENT_ROOT']) && strlen($_SERVER['DOCUMENT_ROOT'])) {
	define("WEB_ROOT", $_SERVER['DOCUMENT_ROOT']);
}
else {
	$filePath = realpath(dirname(__FILE__) . "/../../html/");
	define("WEB_ROOT", $filePath);
}

// Load classes
spl_autoload_register('customAutoLoader');

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


/**
 * Error
 */

function customError($no, $string, $file, $line, $context) {

	$config = Config::getInstance();
	if($config->isDevServer) {
		$api = php_sapi_name();
		if($api == 'cli') {
			$hError = new ErrorHandler(ErrorHandler::CLI);
		}
		else {
			$hError = new ErrorHandler(ErrorHandler::WEB);
		}
	}
	else {
		$hError = new ErrorHandler(ErrorHandler::MAIL);
	}

	$hError->no = $no;
	$hError->string = $string;
	$hError->file = $file;
	$hError->line = $line;
	$hError->context = $context;
	$hError->mailTo = ERROR_MAIL_TO;
	$hError->output();
}


function customAutoLoader($className) {
	$locations = array(
		DIR_FRAMEWORK . '/class/',
		DIR_FRAMEWORK . '/class/Smarty3/',
		DIR_BASE . "/class/"
	);
	$found = FALSE;
	foreach($locations as $loc) {
		$filePath = $loc . $className . ".php";
		if(file_exists($filePath)){
			require $filePath;
			$found = TRUE;
			break;
		}
	}
}

?>