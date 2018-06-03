<?php
define("DIR_INIT", dirname(__FILE__));  // The main init directory
define("DIR_BASE", realpath(DIR_INIT . "/.."));  // The project's base directory

require(DIR_BASE . "/vendor/tigerduck42/mm-framework/src/init/global.php");

$redisClass = '../../fallback/Redis.php';
if (file_exists($redisClass)) {
  require_once($redisClass);
}
