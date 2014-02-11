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
use mmFramework as fw;

require_once(DIR_FRAMEWORK . "/class/db/DBCore.php");

class Database {

	public static function getInstance($dbName=NULL) {
  		$config = fw\Config::getInstance();
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


	public function __get($name) {
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

	private function _connect() {
		$config = Config::getInstance();
		$this->_link = new mysqli($config->dbHost, $config->dbUser, $config->dbPassword, $config->dbName, $config->dbPort);

		if ($this->_link->connect_error) {
			trigger_error('Connect Error (' . $this->_link->connect_errno . ') ' . $this->_link->connect_error, E_USER_ERROR);
		}
	}

	public function thread_id() {
	  return $this->_link->thread_id;
	}


	public function query($sql) {
		$queryType = '';
		if(preg_match('{\s*(\S+?)\s+}',$sql,$match)) {
			$queryType = strtolower($match[1]);
		}

		switch($queryType) {
			case 'select':
				return $this->_query($sql);
				break;
			default:
				trigger_error("Query: " . $queryType . " not allowed use proper warpper method!", E_USER_ERROR);
				break;
		}
	}


	public function insert($table, $row) {
		$quoted = $this->_quoteValues($row);

		$sql = "INSERT INTO " . $table;
		$sql .= " (" . implode(array_keys($quoted), ', ') . ")";
		$sql .= " VALUES (" . implode($quoted , ", ") . ")";
		$this->_query($sql);

		return $this->insertId;
	}


	public function update($table, $row, $id) {
		$quoted = $this->_quoteValues($row);

		$sql = "UPDATE " . $table . " SET ";
		foreach($quoted as $key => $value) {
			$sql .= $key . " = " . $value . ", ";
		}

		$sql = rtrim($sql, ", ");

		$sql .= " WHERE " . $table . "_id = " . $id;

		$this->_query($sql);
	}


	private function _quoteValues($row) {
		$quoted = array();
		foreach($row as $key => $value) {
			if(is_string($value)) {
				$quoted[$key] = "'" . $this->_link->real_escape_string($value) ."'";
			}
			else if(is_bool($value)) {
				if($value == TRUE) {
					$quoted[$key] = 1;
				}
				else {
					$quoted[$key] = 0;
				}
			}
			else if(is_numeric($key)) {
				$quoted[$key] = $value;
			}
			else {
				$quoted[$key] = "'" . $value . "'";
			}
		}

		return $quoted;
	}


	private function _query($sql) {

		$mtime = microtime();
		$mtime = explode(' ', $mtime);
		$mtime = $mtime[1] + $mtime[0];
		$starttime = $mtime;

		$queryType = '';
		if(preg_match('{\s*(\S+?)\s+}',$sql,$match)) {
			$queryType = strtolower($match[1]);
		}

		if (is_null($this->_link)) {
			$this->_connect();
		}

		$this->_insertId = 0;
		$this->_rows = 0;
		if (!($this->_resultHandle = $this->_link->query($sql,MYSQLI_STORE_RESULT))) {
			$mtime = microtime();
			$mtime = explode(" ", $mtime);
			$mtime = $mtime[1] + $mtime[0];
			$endtime = $mtime;
			$totaltime = ($endtime - $starttime);

			trigger_error('Query Failed<br/>
								<b>Time:</b> ' . date('l dS \of F Y h:i:s A') . '<br/>
								<b>URI:</b> ' . $_SERVER['REQUEST_URI'] . '<br/>
								<b>Remote Address:</b> '  . $_SERVER["REMOTE_ADDR"] . '<br/>
								<b>SQL:</b> ' . $sql . '<br/>
								<b>Total Time:</b> ' . $totaltime . '<br/>
								<b>MySQL Error:</b> (' . $this->_link->errno . ') ' . $this->_link->error . "<br/>\n" , E_USER_ERROR);

			$this->_resultHandle = NULL;
		}
		else {
			switch($queryType) {
				case 'select':
					$this->_rows = $this->_resultHandle->num_rows;
					break;
				case 'insert':
					$this->_insertId =  $this->_link->insert_id;

				case 'update':
				case 'delete':
					$this->_affectedRows = $this->_link->affected_rows;
					break;
			}
		}

		return $this->_resultHandle;
	}


	public function asfetch() {
		if(!is_null($this->_resultHandle)) {
			$this->_result = $this->_resultHandle->fetch_assoc();
			return $this->_result;
		} else {
			return FALSE;
		}
	}



  public function close() {
     $this->_link->close();
  }



  private function _sendErrorEmail($subject, $message) {
	  $mail = new MyMailer();
	  $mail->From = ERROR_MAIL_TO;
	  $mail->FromName = "WEBError";
	  $mail->AddAddress(ERROR_MAIL_TO, "WebAdmin");
	  $mail->Subject = $subject;
	  $mail->IsHTML();

	  $mail->Body = "<html><head></head><body style='font-family: \"Courier New\", Courier, monospace;'>" . $message . "</body></html>";

	  if(!$mail->Send()) {
		  echo $mail->ErrorInfo;
	  }
  }

}
?>
