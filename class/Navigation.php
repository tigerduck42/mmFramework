<?php

/**
 * Description of navigation
 *
 * @author martin
 */
class Navigation {
	private $_navigationList = array();
	
	public function __construct() {
	}	
	
	public function getList() {
		return $this->_navigationList;
	}
	
	public function addItem($name, $url, $class="") {
		$this->_navigationList[] = new navigationItem($name, $url, $class);
	}
	
}


class navigationItem {
	private $_name;
	private $_url;
	private $_class;
	
	
	public function __construct($name,$url, $class="") {
		$this->_name = $name;
		$this->_url = $url;
		$this->_class = $class;
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
			default:
				throw new Exception("Property " . $name . " not defined!" , 2);
				break;
		}
	}
}

?>
