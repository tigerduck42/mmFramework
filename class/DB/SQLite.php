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

class SQLite extends DBCore {

	protected function _connect($dbName=NULL) {
		$config = Config::getInstance();

		if(is_null($dbName)) {
			$databaseDir = DIR_BASE . "/db/";
			if(!file_exists($databaseDir)) {
				mkdir($databaseDir);
			}
			$dbName = $databaseDir . $config->dbName;
		}

		try {
			$this->_link = new SQLite3($dbName);
		}
		catch(exception $e) {
			trigger_error('Connect Error ' . $e->getMessage(), E_USER_ERROR);
		}
	}

	public function asfetch() {
		if(!is_null($this->_resultHandle)) {
			$this->_result = $this->_resultHandle->fetchArray(SQLITE3_ASSOC);
			return $this->_result;
		} else {
			return FALSE;
		}
	}

	protected function _q($sql) {
		return $this->_link->query($sql);
	}

	protected function _escape($value) {
		return $this->_link->escapeString($value);
	}

	protected function _rows() {
		return $this->_link->changes();
	}

	protected function _affectedRows(){
		return $this->_link->changes();
	}
	protected function _insertId() {
		return $this->_link->lastInsertRowID();
	}

	protected function _errorNo() {
		return $this->_link->lastErrorCode();
	}
	protected function _errorMsg() {
		return $this->_link->lastErrorMsg();
	}
}
?>