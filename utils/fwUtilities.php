<?php

namespace mmFramework;

/**
 * Error
 */

function customError($severity, $message, $file, $line, $context)
{
  $config = Config::getInstance();

  if ($config->errorToExeptions) {
    if ($config->ignoreExections) {
      return;
    }

    if (!(error_reporting() & $severity)) {
      // This severity is not in included in error_reporting
      //return;
    }

    // Don't spill exception on deprecated warnings
    if (!((E_DEPRECATED | E_USER_DEPRECATED | E_USER_ERROR | E_USER_NOTICE | E_USER_WARNING) & $severity)) {
      throw new \ErrorException($message, 0, $severity, $file, $line);
      return;
    }
  }

  customErrorMessage(NULL, $message, $file, $line, $context, $severity);
}

function customErrorMessage($no, $message, $file, $line, $context, $severity = NULL)
{
  $config = Config::getInstance();
  $api = php_sapi_name();

  if ($config->isDevServer) {
    if ($api == 'cli') {
      $errorHandle = ErrorHandle::CLI;
    } else {
      $errorHandle = ErrorHandle::WEB | ErrorHandle::LOG;
    }
  } else {
    if ($api == 'cli') {
      $errorHandle = ErrorHandle::CLI;
      if ($config->hasMailConfigured) {
        $errorHandle |= ErrorHandle::MAIL;
      }
    } else {
      $errorHandle = ErrorHandle::LOG;
      if ($config->hasMailConfigured) {
        $errorHandle |= ErrorHandle::MAIL;
      }
    }
  }

  $hError = new ErrorHandle($errorHandle);

  $hError->no       = $no;
  $hError->severity = $severity;
  $hError->string   = $message;
  $hError->file     = $file;
  $hError->line     = $line;
  $hError->context  = $context;
  $hError->mailTo   = $config->errorEmail;
  $hError->output();
}


function softException($exception)
{
  $code    = $exception->getCode();
  $message = $exception->getMessage();
  $file    = $exception->getFile();
  $line    = $exception->getLine();
  customErrorMessage($code, $message, $file, $line, NULL, E_USER_ERROR);
}

/**
 * Custom Exception handling
 * @param  exception $ex the thrown exception
 */
function customException($ex)
{
  //ini_set('memory_limit', '-1');
  $errorString  = "Uncaught " . $ex->__toString();
  $errorString .= " thrown in <b>" . $ex->getFile() . "</b> on line <b>" . $ex->getLine() . "</b><br/>";

  // Add _GET, _POST and _SERVER stack
  if (!empty($_GET)) {
    $errorString .=  "\nGET: " . print_r($_GET, TRUE);
  }

  if (!empty($_POST)) {
    $errorString .=  "\nPOST: " . print_r($_POST, TRUE);
  }


  $api = php_sapi_name();
  if ($api != 'cli') {
    // Copy it across to have all values on a later stage
    $myServer = $_SERVER;

    // Remove some parts from
    unset($myServer['LS_COLORS']);

    if (!empty($myServer)) {
      $errorString .=  "\nSERVER: " . print_r($myServer, TRUE);
    }
  }

  $traceStack = $ex->getTrace();
  $reducedStack = array();
  foreach ($traceStack as $node) {
    if (isset($node['args'])) {
      unset($node['args']);
    }
    $reducedStack[] = $node;
  }

  $severity = NULL;
  if (get_class($ex) == "ErrorException") {
    $severity = $ex->getSeverity();
  }
  customErrorMessage($ex->getCode(), nl2br($errorString), $ex->getFile(), $ex->getLine(), $reducedStack, $severity);

  $config = Config::getInstance();
  $api = php_sapi_name();
  if (!$config->isDevServer && ($api != 'cli')) {
    if (file_exists(DIR_BASE . '/html/error.php')) {
      $errorHash = urlencode(base64_encode(serialize('Error: ' . $errorString)));
      HTTP::redirect('/error.php?ec=500&eh=' . $errorHash);
    } else {
      echo "Fatal Error: " . $errorString;
    }
  }
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
