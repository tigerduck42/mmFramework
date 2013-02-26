<?php

/**
 * Description of navigation
 *
 * @author martin
 */
class Navigation {
	private $_list = array();
		
	public function __construct() {
	}	
	
	function __get($name) {
		switch($name) {
			case "list":
				return $this->_list;
				break;
			default:
				throw new Exception("Property " . $name . " not defined!" , 2);
				break;
		}
	}
	

	public function addItem($name, $url, $class="") {
		$node = new navigationItem($name, $url, $class);
		$this->_list[] = $node;
		return $node->children;
	}
	
}


class navigationItem {
	private $_name;
	private $_url;
	private $_class;
	private $_children;
	
	
	public function __construct($name,$url, $class="") {
		$this->_name = $name;
		$this->_url = $url;
		$this->_class = $class;
		$this->_children = new Navigation();
	}
	
	
	function __get($name) {
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
			default:
				throw new Exception("Property " . $name . " not defined!" , 2);
				break;
		}
	}
}

?>
