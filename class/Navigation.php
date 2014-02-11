<?php
/**
 * The MIT License (MIT)
 * Copyright (c) 2013 Martin Mitterhauser
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 *
 *
 * @link https://github.com/tigerduck42/mmFramework
 * @copyright 2013 Martin Mitterhauser
 * @author Martin Mitterhauser <martin.mitterhauser at gmail.com>
 * @package MmFramework
 * @version 1.0
 */

namespace mmFramework;

class Navigation {

	private $_list = array();
	private $_level = NULL;

	public function __construct() {
		$this->_level = 0;
	}

	function __get($name) {
		switch($name) {
			case "list":
				return $this->_list;
				break;
			case 'level':
				return $this->_level;
				break;
			default:
				throw new Exception(__METHOD__ . " - Property " . $name . " not defined!" , 2);
				break;
		}
	}


	public function addItem($name, $url, $class="") {
		$node = new navigationItem($name, $url, $class);
		$this->_list[] = $node;
		$node->children->_level = $this->_level +1;
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
				throw new Exception(__METHOD__ . " - Property " . $name . " not defined!" , 2);
				break;
		}
	}
}

?>
