<?php

function print_r_nice($data, $return = FALSE)
{
  if (TRUE == $return) {
    return echo_nice(print_r($data, TRUE));
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
