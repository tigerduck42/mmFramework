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
 * @version 2.0
 */
namespace mmFramework\DB;

class FilterItem
{
  private static $_opStack = array(
    'eq' => '=',
    'gt' => '>',
    'ge' => '>=',
    'lt' => '<',
    'le' => '<='
  );


  private $_key      = NULL;
  private $_value    = NULL;
  private $_operator = NULL;

  public function __construct()
  {
    $this->_operator = '=';
  }

  public function __get($name)
  {
    switch($name) {
      case 'key':
        return '`' . $this->_key .'`';
        break;
      case 'operator':
        return $this->_operator;
        break;
      case 'value':
        return $this->_fixValue();
        break;
      default:
        throw new Exception(get_class($this) . "::__get() - Property " . $name . " not defined!");
        break;
    }
  }

  public function __set($name, $value)
  {
    switch($name) {
      case 'key':
        $this->_key = $value;
        break;
      case 'value':
        $this->_value = $value;
        break;
      case 'operator':
        if (in_array($value, array_keys(self::$_opStack))) {
          $this->_operator = self::$_opStack[$value];
        } else {
          throw new Exception(get_class($this) . ":: - Operator " . $value . " not defined!");
        }
        break;
      default:
        throw new Exception(get_class($this) . "::__set() - Property " . $name . " not defined!");
        break;
    }
  }

  private function _fixValue()
  {
    $value = $this->_value;
    if (is_null($value)) {
      $this->_operator = 'is NULL';
      $value = '';
    } else if (!is_numeric($value)) {
      $value = "'" . $value . "'";
    }

    return $value;
  }
}
