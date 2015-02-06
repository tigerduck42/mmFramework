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

class Filter
{
  private $_filterStack = array();

  public function __construct()
  {

  }

  public function __get($name)
  {
    switch($name) {

      default:
        throw new Exception(get_class($this) . "::__get() - Property " . $name . " not defined!");
        break;
    }
  }

  public function __set($name, $value)
  {
    switch($name) {
      default:
        throw new Exception(get_class($this) . "::__set() - Property " . $name . " not defined!");
        break;
    }
  }


  public function add($key, $value, $operator = NULL, $tag = NULL)
  {
    $item = new FilterItem();
    $item->key = $key;
    $item->value = $value;
    if (!is_null($operator)) {
      $item->operator = $operator;
    }

    if (!is_null($tag)) {
      if (isset($this->_filterStack[$tag])) {
        throw new Exception(get_class($this) . ":: - Tag " . $tag . " already used!");
      }
      $this->_filterStack[$tag] = $item;
    } else {
      $this->_filterStack[] = $item;
    }
  }

  public function remove($tag)
  {
    if (isset($this->_filterStack[$tag])) {
      unset($this->_filterStack[$tag]);
    }
  }

  public function buildQuery()
  {
    $sql = "";

    foreach ($this->_filterStack as $filter) {
      $sql .= " AND " . $filter->key . " " . $filter->operator . " " . $filter->value;
    }

    return $sql;
  }
}
