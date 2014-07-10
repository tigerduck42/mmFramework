<?php
namespace mmFramework;

class NavigationItem
{
  private $_name;
  private $_url;
  private $_class;
  private $_children;

  public function __construct($name, $url, $class = "")
  {
    $this->_name = $name;
    $this->_url = $url;
    $this->_class = $class;
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
        throw new Exception(__METHOD__ . " - Property " . $name . " not defined!", 2);
        break;
    }
  }
}
