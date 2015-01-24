<?php

namespace mmFramework;

/**
 * Error
 */

function customError($no, $string, $file, $line, $context)
{
  $config = Config::getInstance();
  $api = php_sapi_name();

  if ($config->isDevServer) {
    if ($api == 'cli') {
      $hError = new ErrorHandler(ErrorHandler::CLI);
    } else {
      $hError = new ErrorHandler(ErrorHandler::WEB | ErrorHandler::LOG);
    }
  } else {
    if ($api == 'cli') {
      $hError = new ErrorHandler(ErrorHandler::CLI | ErrorHandler::MAIL);
    } else {
      $hError = new ErrorHandler(ErrorHandler::MAIL);
    }
  }

  $hError->no       = $no;
  $hError->string   = $string;
  $hError->file     = $file;
  $hError->line     = $line;
  $hError->context  = $context;
  $hError->mailTo   = $config->errorEmail;
  $hError->output();
}

/**
 * Custom Exception handling
 * @param  exception $ex the thrown exception
 */
function customException($ex)
{
  //ini_set('memory_limit', '-1');
  $errorString  = ": Uncaught " . $ex->__toString();
  $errorString .= " thrown in <b>" . $ex->getFile() . "</b> on line <b>" . $ex->getLine() . "</b><br/>";

  $traceStack = $ex->getTrace();
  $reducedStack = array();
  foreach ($traceStack as $node) {
    if (isset($node['args'])) {
      unset($node['args']);
    }
    $reducedStack[] = $node;
  }

  customError(0, nl2br($errorString), $ex->getFile(), $ex->getLine(), $reducedStack);
}


/**
 * Custom autoloader
 * @param  string $fullClassName Class name
 */
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
  //  Smalot PDF Parser
  //
  if (preg_match('{^(Smalot\\\.+)$}', $fullClassName, $m)) {
    $className = $m[1];
    $classPath = preg_replace('{\\\}', '/', $className);
    $filePath = DIR_FRAMEWORK . '/thirdParty/' . $classPath . ".php";

    //require_once(DIR_FRAMEWORK . '/thirdParty/tcpdf/tcpdf_autoconfig.php');
    require_once(DIR_FRAMEWORK . '/thirdParty/tcpdf/tcpdf_parser.php');
  }

  //
  // Local app classes
  //
  if (preg_match('{^app\\\(.+)$}', $fullClassName, $m)) {
    $className = $m[1];
    $classPath = preg_replace('{\\\}', '/', $className);
    $filePath = DIR_BASE . '/class/' . $classPath . ".php";
  }

  //echo $filePath . "<br/>";

  if (!is_null($filePath) && file_exists($filePath)) {
    require_once($filePath);
  }
}
