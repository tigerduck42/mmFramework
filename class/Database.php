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

class Database
{
  public static function getInstance($dbName = NULL)
  {
    $config = Config::getInstance();
    switch(strtolower($config->dbConnector)) {
      case 'sqllite':
        require_once(DIR_FRAMEWORK . "/class/db/SQLite.php");
        return new DB\SQLite($dbName);
        break;
      case 'mysql':
        require_once(DIR_FRAMEWORK . "/class/db/MySQL.php");
        return new DB\MySQL($dbName);
        break;
      default:
        throw new exception(__CLASS__ . " - Connector not defined!");
        break;

    }
  }


  public function __get($name)
  {
    switch($name) {
      case 'insertId':
        return $this->_insertId;
        break;
      case 'affectedRows':
        return $this->_affectedRows;
        break;
      case 'rows':
        return $this->_rows;
        break;
      case 'link':
        return $this->_link;
        break;
      default:
        throw new exception(__CLASS__ . "::Get - Attribute " . $name . " not defined!");
        break;
    }
  }
}
