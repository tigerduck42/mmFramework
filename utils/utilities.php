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
    $data .= "\n";
  } else {
    $data = '<pre>' . $data . '</pre>';
  }
  if ($return) {
    return $data;
  } else {
    echo $data;
  }
}

function intNice($value)
{
  if (!is_null($value)) {
    return (int)$value;
  }
  return $value;
}

function waitForMe()
{
  echo_nice("Press Key to continue.");
  $handle = fopen("php://stdin", "r");
  $line = fgets($handle);
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
