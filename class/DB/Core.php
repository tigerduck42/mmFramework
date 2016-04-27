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

namespace mmFramework\DB;

use mmFramework as fw;

abstract class Core
{
  protected $_dbName        = NULL;
  protected $_link          = NULL;
  protected $_rows          = NULL;
  protected $_affectedRows  = NULL;
  protected $_resultHandle  = NULL;
  protected $_result        = NULL;
  protected $_insertId      = NULL;

  protected $_statement     = NULL;

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
      case 'statement':
        return $this->_statement;
        break;
      case 'threadId':
        return $this->_threadId();
        break;
      case 'success':
        return (is_object($this->_resultHandle) || (TRUE === $this->_resultHandle));
        break;
      default:
        throw new Exception(__CLASS__ . "::Get - Attribute " . $name . " not defined!");
        break;
    }
  }

  abstract public function asFetch();
  abstract public function asFetchAll();
  abstract public function objFetch();

  abstract protected function _connect();
  abstract protected function _q($sql);
  abstract protected function _qMulti($sql);
  abstract protected function _escape($value);
  abstract protected function _rows();
  abstract protected function _affectedRows();
  abstract protected function _insertId();
  abstract protected function _errorNo();
  abstract protected function _errorMsg();

  abstract protected function _prepare($sql);
  abstract protected function _bindParam(&$params);
  abstract protected function _execute();

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

  public function queryMulti($sql)
  {
    return $this->_queryMulti($sql);
  }


  public function insert($table, $row)
  {
    $quoted = $this->_quoteValues($row);

    $sql = "INSERT INTO `" . $table . "`";
    $sql .= " (" . implode(array_keys($quoted), ', ') . ")";
    $sql .= " VALUES (" . implode($quoted, ", ") . ")";
    $this->_query($sql);
    //echo_nice($sql);
    return $this->insertId;
  }

  public function insertMutex($table, $row, $uKey, $uValue)
  {
    $quoted = $this->_quoteValues($row);

    $sql = "INSERT INTO `" . $table . "`";
    $sql .= " (" . implode(array_keys($quoted), ', ') . ")";
    $sql .= " VALUES (" . implode($quoted, ", ") . ")";
    $sql .= " ON DUPLICATE KEY UPDATE `" . $uKey ."` = '" . $uValue . "'";
    $this->_query($sql);
    //echo_nice($sql);
    return $this->insertId;
  }

  public function replace($table, $row)
  {
    $quoted = $this->_quoteValues($row);

    $sql = "REPLACE INTO `" . $table . "`";
    $sql .= " (" . implode(array_keys($quoted), ', ') . ")";
    $sql .= " VALUES (" . implode($quoted, ", ") . ")";
    $this->_query($sql);
  }

  public function update($table, $row, $id, $customIdName = NULL)
  {
    $quoted = $this->_quoteValues($row);

    $sql = "UPDATE `" . $table . "` SET ";
    foreach ($quoted as $key => $value) {
      $sql .= $key . " = " . $value . ", ";
    }

    $sql = rtrim($sql, ", ");

    if (is_null($customIdName)) {
      $sql .= " WHERE `" . $table . "_id` = " . $id;
    } else {
      $sql .= " WHERE `" . $customIdName . "` = ";

      // proper wrapping if key is a string
      if (is_string($id)) {
        $sql .=  "'" . $id ."'";
      } else {
        $sql .=  $id;
      }
    }
    //echo_nice($sql);
    $this->_query($sql);
  }

  public function delete($table, $id, $customIdName = NULL)
  {
    if (!is_null($id)) {
      $sql = "DELETE FROM `" . $table . "` ";

      if (is_null($customIdName)) {
        $sql .= " WHERE `" . $table . "_id` = " . $id;
      } else {
        $sql .= " WHERE `" . $customIdName . "` = ";

        // proper wrapping if key is a string
        if (is_string($id)) {
          $sql .=  "'" . $id ."'";
        } else {
          $sql .=  $id;
        }
      }
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


  private function _query($sql)
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

    $this->_insertId = NULL;
    $this->_rows = 0;
    if (!($this->_resultHandle = $this->_q($sql))) {
      $mtime = microtime();
      $mtime = explode(" ", $mtime);
      $mtime = $mtime[1] + $mtime[0];
      $endtime = $mtime;
      $totaltime = ($endtime - $starttime);


      $errorMessage =
        'Query Failed<br/>
        <strong>URI:</strong> ' . fw\HTTP::server('REQUEST_URI') . '<br/>
        <strong>Remote Address:</strong> '  . fw\HTTP::server("REMOTE_ADDR") . '<br/>
        <strong>Database:</strong> ' . $this->_dbName . '<br/>
        <strong>SQL:</strong> ' . $sql . '<br/>
        <strong>Total Time:</strong> ' . $totaltime . '<br/>
        <strong>MySQL Error:</strong> (' . $this->_errorNo() . ') ' . $this->_errorMsg() . "<br/>\n";

      if ($this->_inTransaction) {
        throw new Exception($errorMessage);
      } else {
        trigger_error($errorMessage, E_USER_ERROR);
      }

      $this->_resultHandle = FALSE;
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

  private function _queryMulti($sql)
  {
    return $this->_qMulti($sql);
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

  public function bindParam()
  {
    $params = func_get_args();
    return $this->_bindParam($params);
  }

  public function execute()
  {
    return $this->_execute();
  }

  public function escape($value)
  {
    return $this->_escape($value);
  }

  public function info()
  {
    $info = array();
    $info['threadId'] = $this->threadId;
    $info['inTransaction'] = $this->_inTransaction;

    return $info;
  }
}
