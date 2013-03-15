<?php

   
abstract class DBCore {
	protected $_link = NULL;
	protected $_rows = NULL;
	protected $_affectedRows = NULL;
	protected $_resultHandle = NULL;
	protected $_result = NULL;
	protected $_insertId = NULL;

	public function __construct($dbName=NULL) {
  		if(is_null($this->_link)) {
			$this->_connect($dbName);
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
	
	abstract public function asfetch();
	
	abstract protected function _connect();
	abstract protected function _q($sql); 
	abstract protected function _escape($value);
	abstract protected function _rows();
	abstract protected function _affectedRows();
	abstract protected function _insertId();
	abstract protected function _errorNo();
	abstract protected function _errorMsg();
	
	public function query($sql,$force=FALSE) {
		$queryType = '';
		if(preg_match('{\s*(\S+?)\s+}',$sql,$match)) {
			$queryType = strtolower($match[1]);
		}
		
		if($force) {
			return $this->_query($sql);
		}
		else {
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
				$quoted[$key] = "'" . $this->_escape($value) ."'";
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
		if (!($this->_resultHandle = $this->_q($sql))) {
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
								<b>MySQL Error:</b> (' . $this->_errorNo() . ') ' . $this->_errorMsg() . "<br/>\n" , E_USER_ERROR);
			
			$this->_resultHandle = NULL;
		} 
		else {
			switch($queryType) {
				case 'select':
					$this->_rows = $this->_rows();
					break;
				case 'insert':
					$this->_insertId =  $this->_insertId();
					
				case 'update':
				case 'delete':
					$this->_affectedRows = $this->_affectedRows();
					break;
			}
		}

		return $this->_resultHandle;
	}

  public function close() {
     $this->_link->close();
  } 

 
  /*
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
  */
}
?>