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

class MySQL extends DBCore {

	protected function _connect($dbName=NULL) {
		$config = Config::getInstance();

		if(is_null($dbName)) {
			$dbName = $config->dbName;
		}

		$this->_link = new mysqli($config->dbHost, $config->dbUser, $config->dbPassword, $dbName, $config->dbPort);

		if ($this->_link->connect_error) {
			trigger_error('Connect Error (' . $this->_link->connect_errno . ') ' . $this->_link->connect_error, E_USER_ERROR);
		}
	}

	public function asfetch() {
		if(!is_null($this->_resultHandle)) {
			$this->_result = $this->_resultHandle->fetch_assoc();
			return $this->_result;
		} else {
			return FALSE;
		}
	}

	protected function _q($sql) {
		return $this->_link->query($sql,MYSQLI_STORE_RESULT);
	}

	protected function _escape($value) {
		return $this->_link->real_escape_string($value);
	}

	protected function _rows() {
		return $this->_resultHandle->num_rows;
	}

	protected function _affectedRows(){
		return $this->_link->affected_rows;
	}

	protected function _insertId() {
		return $this->_link->insert_id;
	}

	protected function _errorNo() {
		return $this->_link->errno;
	}

	protected function _errorMsg() {
		return $this->_link->error;
	}

  public function beginTransaction() {
  	if ($this->_inTransaction) {
  		throw new exception("Already in transaction!");
  	}

  	$this->_link->autocommit(FALSE);
  	$this->_inTransaction = TRUE;
  }

  public function commit() {
  	$this->_endTransaction('commit'); 
  }

  public function rollback() {
  	$this->_endTransaction('rollback'); 
  }

  private function _endTransaction($type) {
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

}

?>