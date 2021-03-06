<?php
use mmFramework as fw;

function print_r_nice($data, $return = FALSE)
{
  if (TRUE == $return) {
    return echo_nice(print_r($data, TRUE), TRUE);
  } else {
    echo echo_nice(print_r($data, TRUE));
  }
}

function echo_nice($data, $return = FALSE)
{
  $api = php_sapi_name();
  if (preg_match('{cli}', $api)) {
    $data = html_entity_decode($data) . "\n";
  } else {
    $data = '<pre>' . $data . '</pre>';
  }
  if ($return) {
    return $data;
  } else {
    echo $data;
  }
}

function echo_exit($data)
{
  echo_nice($data);
  exit;
}

function email_nice($data, $subject = NULL)
{
  $config = fw\Config::getInstance();
  $body = '';
  if (is_array($data) || is_object($data)) {
    $body .= print_r_nice($data, TRUE);
  } else {
    $body .= echo_nice($data, TRUE);
  }


  $mail = new fw\MyMailer();
  $mail->From = $config->errorEmail;
  $mail->FromName = "DebugNotify";
  $mail->AddAddress($config->errorEmail, "WebAdmin");
  $mail->Subject = "";
  $mail->Subject .= "[DEBUG] " . fw\HTTP::hostname();

  if (!is_null($subject)) {
    $mail->Subject .= " - " . $subject;
  }

  $mail->IsHTML();
  $mail->setBody($body);

  if (!$mail->Send()) {
    trigger_error($mail->ErrorInfo, E_USER_ERROR);
  }
}

function intNice($value)
{
  if (!is_null($value)) {
    return (int)$value;
  }
  return $value;
}

function waitForMe($question = NULL)
{
  if (is_null($question)) {
    echo_nice("Press Key to continue.");
  } else {
    echo($question . " ");
  }
  $handle = fopen("php://stdin", "r");
  $char = fgetc($handle);
  return $char;
}

function substrAdv($str, &$start, $len)
{
  $subString = trim(substr($str, $start, $len));
  $start += $len;
  return $subString;
}

function truncate($string, $maxLen, $ending = "...")
{
  $endingLen = strlen($ending);
  $truncLen  = $maxLen - $endingLen;
  $stringLen = strlen($string);
  if ($stringLen > $maxLen) {
    $string = substr($string, 0, $truncLen) . $ending;
  }

  return $string;
}

function myObClean()
{
  $debugCode = NULL;
  if (ob_get_length()) {
    $debugCode = ob_get_contents();

    $noticeHandler = new fw\ErrorHandle();
    $page = new fw\Page();
    $msg = '<strong>Found something hidden on the following page "' . $page->url . '"</strong>
    <pre>' . $debugCode . '</pre>';
    $noticeHandler->send($msg);

    ob_clean();
  }
  return $debugCode;
}
