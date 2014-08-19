<?php

namespace mmFramework;

/**
 * Error
 */

function customError($no, $string, $file, $line, $context)
{
  $config = Config::getInstance();
  if ($config->isDevServer) {
    $api = php_sapi_name();
    if ($api == 'cli') {
      $hError = new ErrorHandler(ErrorHandler::CLI);
    } else {
      $hError = new ErrorHandler(ErrorHandler::WEB | ErrorHandler::LOG);
    }
  } else {
    $hError = new ErrorHandler(ErrorHandler::MAIL);
  }

  $hError->no       = $no;
  $hError->string   = $string;
  $hError->file     = $file;
  $hError->line     = $line;
  $hError->context  = $context;
  $hError->mailTo   = $config->errorEmail;
  $hError->output();
}


function customAutoLoader($fullClassName)
{
  $filePath = NULL;
  //
  // mmFramework classes
  //
  if (preg_match('{^mmFramework\\\(.+)$}', $fullClassName, $m)) {
    $className = $m[1];
    $classPath = preg_replace('{\\\}', '/', $className);
    $filePath = DIR_FRAMEWORK . '/class/' . $classPath . ".php";
  }

  //
  // Local app classes
  if (preg_match('{^app\\\(.+)$}', $fullClassName, $m)) {
    $className = $m[1];
    $classPath = preg_replace('{\\\}', '/', $className);
    $filePath = DIR_BASE . '/class/' . $classPath . ".php";
  }

  //echo $filePath . "<br/>";

  if (!is_null($filePath) && file_exists($filePath)) {
    require_once $filePath;
  }
}
