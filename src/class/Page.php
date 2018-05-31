<?php
/**
 * The MIT License (MIT)
 * Copyright (c) 2015
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
 * @copyright 2015 Martin Mitterhauser
 * @author Martin Mitterhauser <martin.mitterhauser (at) gmail.com>
 * @package MmFramework
 * @version 2.0
 */

namespace tigerduck\mmFramework;

class Page
{

  protected $_query    = NULL;
  protected $_fullName = NULL;
  protected $_parts    = NULL;

  public function __construct($url = NULL)
  {
    if (is_null($url)) {
      $this->_query    = HTTP::server('QUERY_STRING');
      $this->_fullName = HTTP::server('SCRIPT_NAME');
    } else {
      $parts = explode('?', $url);
      $this->_fullName = $parts[0];
      if (isset($parts[1])) {
        $this->_query = $parts[1];
      }
    }
  }

  public function __get($name)
  {
    switch($name) {
      case "url":
        return $this->_fullName;
        break;
      case "directory":
        if (is_null($this->_parts)) {
          $this->_parts = $this->_split();
        }
        return trim($this->_parts['dirname'], '/');
        break;
      case "name":
        if (is_null($this->_parts)) {
          $this->_parts = $this->_split();
        }
        return $this->_parts['basename'];
        break;
      case "qStack":
        parse_str($this->_query, $stack);
        return $stack;
        break;
      default:
        throw new Exception(__METHOD__ . " - Property " . $name . " not defined!");
        break;
    }
  }

  public function __set($name, $value)
  {
    switch($name) {
      default:
        throw new Exception(__METHOD__ . " - Property " . $name . " not defined!");
        break;
    }
  }

  protected function _split()
  {
    $parts = pathinfo($this->_fullName);
    return $parts;
  }
}
