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
namespace mmFramework\DB;

use  mmFramework as fw;

abstract class DBCore
{
  protected $_link = NULL;
  protected $_rows = NULL;
  protected $_affectedRows = NULL;
  protected $_resultHandle = NULL;
  protected $_result = NULL;
  protected $_insertId = NULL;
  protected $_inTransaction = FALSE;

  public function __construct($dbName = NULL)
  {
    if (is_null($this->_link)) {
      $this->_connect($dbName);
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
      //case 'link':
      //  return $this->_link;
      //  break;
      case 'threadId':
        return $this->_threadId();
        break;
      case 'success':
        return !is_null($this->_resultHandle);
        break;
      default:
        throw new Exception(__CLASS__ . "::Get - Attribute " . $name . " not defined!");
        break;
    }
  }

  abstract public function asfetch();

  abstract protected function _connect();
  abstract protected function _q($sql);
  abstract protected function _escape($value);
  abstract protected function _rows();
  abstract protected function _affectedRows();
  abstract protected function _insertId();
  abstract protected function _errorNo();
  abstract protected function _errorMsg();

  abstract protected function _prepare($sql);

  // Transaction support
  abstract public function beginTransaction();
  abstract public function commit();
  abstract public function rollback();


  public function query($sql, $force = FALSE)
  {
    $queryType = '';
    if (preg_match('{\s*(\S+?)\s+}', $sql, $match)) {
      $queryType = strtolower($match[1]);
    }

    if ($force) {
      return $this->_query($sql);
    } else {
      switch($queryType) {
        case 'select':
          return $this->_query($sql);
          break;
        default:
          trigger_error("Query: " . $queryType . " not allowed use proper wrapper method!", E_USER_ERROR);
          break;
      }
    }
  }


  public function insert($table, $row)
  {
    $quoted = $this->_quoteValues($row);

    $sql = "INSERT INTO " . $table;
    $sql .= " (" . implode(array_keys($quoted), ', ') . ")";
    $sql .= " VALUES (" . implode($quoted, ", ") . ")";
    $this->_query($sql);

    return $this->insertId;
  }


  public function update($table, $row, $id, $customIdName = NULL)
  {
    $quoted = $this->_quoteValues($row);

    $sql = "UPDATE " . $table . " SET ";
    foreach ($quoted as $key => $value) {
      $sql .= $key . " = " . $value . ", ";
    }

    $sql = rtrim($sql, ", ");

    if (is_null($customIdName)) {
      $sql .= " WHERE " . $table . "_id = " . $id;
    } else {
      $sql .= " WHERE " . $customIdName . " = ";

      // proper wrapping if key is a string
      if (is_string($id)) {
        $sql .=  "'" . $id ."'";
      } else {
        $sql .=  $id;
      }
    }

    $this->_query($sql);
  }

  public function delete($table, $id)
  {
    if (!is_null($id)) {
      $sql = "DELETE FROM " . $table . " WHERE " . $table . "_id = " . $id;
      $this->_query($sql);
    } else {
       throw new Exception(__METHOD__ . " - Can't delete empty record!");
    }
  }


  protected function _quoteValues($row)
  {
    $quoted = array();
    foreach ($row as $key => $value) {

      // Quote keys properly
      $key = '`' . $key . '`';

      if (is_string($value)) {
        $quoted[$key] = "'" . $this->_escape($value) ."'";
      } else if (is_bool($value)) {
        if ($value == TRUE) {
          $quoted[$key] = 1;
        } else {
          $quoted[$key] = 0;
        }
      } else if (is_null($value)) {
        $quoted[$key] = "NULL";
      } else if (is_numeric($value)) {
        $quoted[$key] = $value;
      } else {
        $quoted[$key] = "'" . $value . "'";
      }
    }

    return $quoted;
  }


  protected function _query($sql)
  {

    $mtime = microtime();
    $mtime = explode(' ', $mtime);
    $mtime = $mtime[1] + $mtime[0];
    $starttime = $mtime;

    $queryType = '';
    if (preg_match('{\s*(\S+?)\s+}', $sql, $match)) {
      $queryType = strtolower($match[1]);
    }

    if (is_null($this->_link)) {
      $this->_connect();
    }

    $this->_insertId = 0;
    $this->_rows = 0;
    if (!($this->_resultHandle = $this->_q($sql))) {
      $mtime = microtime();
      $mtime = explode(" ", $mtime);
      $mtime = $mtime[1] + $mtime[0];
      $endtime = $mtime;
      $totaltime = ($endtime - $starttime);

      trigger_error(
          'Query Failed<br/>
          <b>Time:</b> ' . date('l dS \of F Y h:i:s A') . '<br/>
          <b>URI:</b> ' . fw\HTTP::server('REQUEST_URI') . '<br/>
          <b>Remote Address:</b> '  . fw\HTTP::server("REMOTE_ADDR") . '<br/>
          <b>SQL:</b> ' . $sql . '<br/>
          <b>Total Time:</b> ' . $totaltime . '<br/>
          <b>MySQL Error:</b> (' . $this->_errorNo() . ') ' . $this->_errorMsg() . "<br/>\n",
          E_USER_ERROR
      );

      $this->_resultHandle = NULL;
    } else {
      switch($queryType) {
        case 'select':
          $this->_rows = $this->_rows();
          break;
        case 'insert':
          $this->_insertId =  $this->_insertId();
          break;
        case 'update':
        case 'delete':
          $this->_affectedRows = $this->_affectedRows();
          break;
      }
    }

    return $this->_resultHandle;
  }

  protected function _threadId()
  {
    throw new Exception(__METHOD__ . " - Can't get thread ID");
  }

  public function close()
  {
     $this->_link->close();
  }

  public function prepare($sql)
  {
    return $this->_prepare($sql);
  }
}
