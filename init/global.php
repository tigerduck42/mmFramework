<?php
error_reporting(E_ALL | E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
set_error_handler("customError");

define("WEB_ROOT", $_SERVER['DOCUMENT_ROOT']);

// Load classes
spl_autoload_register('customAutoLoader'); 

// Set correct timezone
$config = Config::getInstance();
date_default_timezone_set($config->timezone);

$template = new MmOutputRendererSmarty();
$template->assign("hostname", $_SERVER["HTTP_HOST"]);

$me = ltrim($_SERVER["PHP_SELF"], "/");
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
		$hError = new ErrorHandler(ErrorHandler::WEB);
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