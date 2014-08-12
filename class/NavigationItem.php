<?php
namespace mmFramework;

class NavigationItem
{
  private $_name;
  private $_url;
  private $_class;
  private $_children;
  private $_optStack = array();

  public function __construct($name, $url, $class = "", $opt = array())
  {
    assert(is_array($opt));

    $this->_name = $name;
    $this->_url = $url;
    $this->_class = $class;
    $this->_optStack = $opt;
    $this->_children = new Navigation();
  }



  public function __get($name)
  {
    switch($name) {
      case 'name':
        return $this->_name;
        break;
      case 'url':
        return $this->_url;
        break;
      case 'class':
        return $this->_class;
        break;
      case 'children':
        return $this->_children;
        break;
      case 'list':
        return $this->_children->list;
        break;
      default:
        if (in_array($name, array_keys($this->_optStack))) {
          return $this->_optStack[$name];
          break;
        }
        throw new Exception(__METHOD__ . " - Property " . $name . " not defined!", 2);
        break;
    }
  }
}
