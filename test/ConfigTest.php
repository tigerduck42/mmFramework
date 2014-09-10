<?php
namespace mmFramework;

$dirBase = realpath(dirname(__FILE__) . "/..");
require_once($dirBase . "/init/global.php");



class StackTest extends \PHPUnit_Framework_TestCase
{

  public function testExitsDbHost()
  {
    $config = Config::getInstance();

    $check = $config->exists("mailOverRide");
    $this->assertEquals(TRUE, $check);

  }

  public function testExitsMailOverRide()
  {
    $config = Config::getInstance();

    $check = $config->exists("mailOverRide");
    $this->assertEquals(TRUE, $check);
  }
}
