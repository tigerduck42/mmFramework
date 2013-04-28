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

class HTTP {

	/*
	 * GET
	 */

	static public function get($name) {
		if(isset($_GET[$name])) {
			return $_GET[$name];
		}
		else {
			return NULL;
		}
	}

	/*
	 * POST
	 */

	static public function post($name) {
		if(isset($_POST[$name])) {
			return $_POST[$name];
		}
		else {
			return NULL;
		}
	}

	/*
	 * Server
	 */

	static public function server($name) {
		if(isset($_SERVER[$name])) {
			return $_SERVER[$name];
		}
		else {
			return NULL;
		}
	}



	/*
	 * Cookie
	 */
	static public function setCookie($name, $value, $expire=0) {
		if(!setcookie($name, $value, $expire)) {
			trigger_error("Cookie " . $name . " could not be set!", E_USER_ERROR);
		}
	}

	static public function getCookie($name) {
		$cookie = NULL;
		if(self::isCookieSet($name)) {
			$cookie = $_COOKIE[$name];
		}

		return $cookie;
	}

	static public function deleteCookie($name) {
		self::setCookie($name, "", time() - 3600);
	}


	static public function isCookieSet($name) {
		return isset($_COOKIE[$name]);
	}

	/*
	 * Session
	 */
	static public function getSession($name) {
		$value = NULL;
		if(self::isSessionSet($name)) {
			$value = $_SESSION[$name];
		}

		return $value;
	}

	static public function isSessionSet($name) {
		return isset($_SESSION[$name]);
	}

	/*
	 * Misc
	 */
	static public function me() {
  	return $_SERVER['SCRIPT_NAME'];
  }

	static public function refresh() {
  	self::redirect(self::me());
  	exit;
  }

  static public function redirect($url) {
  	header("Location: " . $url);
  	exit;
  }

  static public function hostname() {
  	$hostname = "Unknown host";
  	$servername = HTTP::server('SERVER_NAME');
  	if(!is_null($servername)) {
  		$hostname .= " - " . $servername;
  	}

		if(!is_null(HTTP::server('HTTP_HOST'))) {
			$hostname = HTTP::server('HTTP_HOST');
		}

		return $hostname;
  }
}

?>