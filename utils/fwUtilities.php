<?php


namespace mmFramework;

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


function customAutoLoader($fullClassName) {

  $className = preg_replace('{^mmFramework\\\}', '', $fullClassName);

  $locations = array(
    DIR_FRAMEWORK . '/class/',
    DIR_FRAMEWORK . '/class/Smarty3/',
    DIR_BASE . "/class/"
  );
  $found = FALSE;
  foreach ($locations as $loc) {
    $filePath = $loc . $className . ".php";
    if (file_exists($filePath)) {
      
      require $filePath;
      $found = TRUE;
      break;
    }
  }
}



?>