<?php

function print_r_nice($data, $return = FALSE)
{
  if (TRUE == $return) {
    return '<pre>' . print_r($data, TRUE) . '</pre>';
  } else {
    echo '<pre>' . print_r($data, TRUE) . '</pre>';
  }
}

function echo_nice($data)
{
  echo '<pre>' . $data . '</pre>';
}
