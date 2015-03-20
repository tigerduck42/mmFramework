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

class MySQL extends DBCore
{
  private static $_obj = array();

  public static function getInstance($dbConfig = 'default')
  {
    if (isset(self::$_obj[$dbConfig]) && self::$_obj[$dbConfig]->_inTransaction) {
      return self::$_obj[$dbConfig];
    } else {
      $obj = new self($dbConfig);
      self::$_obj[$dbConfig] =& $obj;
      return $obj;
    }
  }

  protected function _connect($dbConfig = 'default')
  {
    $config = fw\Config::getInstance();

    if (!isset($config->dbConfiguration[$dbConfig])) {
      throw new Exception(__METHOD__ . " - No database config '" . $dbConfig . "' defined!");
    }

    $conf = $config->dbConfiguration[$dbConfig];

    $this->_link = new \mysqli($conf['dbHost'], $conf['dbUser'], $conf['dbPassword'], $conf['dbName'], $conf['dbPort']);

    if ($this->_link->connect_error) {
      throw new Exception('Connect Error (' . $this->_link->connect_errno . ') ' . $this->_link->connect_error, E_USER_ERROR);
    }

    $this->_link->set_charset($conf['dbCharset']);

    $this->_checkError();
  }

  public function asFetch()
  {
    if (!is_null($this->_resultHandle)) {
      $this->_result = $this->_resultHandle->fetch_assoc();
      return $this->_result;
    } else {
      return FALSE;
    }
  }

  public function asFetchAll()
  {
    if (!is_null($this->_resultHandle)) {
      $this->_result = $this->_resultHandle->fetch_all(MYSQLI_ASSOC);
      return $this->_result;
    } else {
      return FALSE;
    }
  }

  protected function _q($sql)
  {
    return $this->_link->query($sql, MYSQLI_STORE_RESULT);
  }

  protected function _escape($value)
  {
    return $this->_link->real_escape_string($value);
  }

  protected function _rows()
  {
    return $this->_resultHandle->num_rows;
  }

  protected function _affectedRows()
  {
    return $this->_link->affected_rows;
  }

  protected function _insertId()
  {
    return $this->_link->insert_id;
  }

  protected function _errorNo()
  {
    return $this->_link->errno;
  }

  protected function _errorMsg()
  {
    return $this->_link->error;
  }

  public function beginTransaction()
  {
    if ($this->_inTransaction) {
      throw new exception("Already in transaction!");
    }

    $this->_link->autocommit(FALSE);
    $this->_inTransaction = TRUE;
  }

  public function commit()
  {
    $this->_endTransaction('commit');
  }

  public function rollback()
  {
    $this->_endTransaction('rollback');
  }

  protected function _threadId()
  {
    return $this->_link->thread_id;
  }


  //
  //  Prepare /  Excute
  //

  protected function _prepare($sql)
  {
    $this->_statement = $this->_link->prepare($sql);
    if (!$this->_statement) {
      $this->_checkError();
    }
  }

  protected function _execute()
  {
    $this->_statement->execute();
    $this->_resultHandle = $this->_statement->get_result();
    $this->_rows         = max($this->_statement->num_rows, $this->_statement->affected_rows);
    $this->_affectedRows = $this->_statement->affected_rows;
    $this->_checkError();
  }

  private function _endTransaction($type)
  {
    switch ($type) {
      case 'commit':
        $this->_link->commit();
        break;
      case 'rollback':
        $this->_link->rollback();
        break;
    }

    $this->_link->autocommit(TRUE);
    $this->_inTransaction = FALSE;
  }

  private function _checkError()
  {
    if (count($this->_link->error_list)) {
      foreach ($this->_link->error_list as $eRec) {
        trigger_error('DB Error (' . $eRec['errno'] . ') ' . $eRec['error'], E_USER_ERROR);
      }
    } else {
      if ((0 < strlen($this->_link->error)) || ($this->_link->errno > 0)) {
        trigger_error('DB Error (' . $this->_link->errno . ') ' . $this->_link->error, E_USER_ERROR);
      }
    }
  }
}
