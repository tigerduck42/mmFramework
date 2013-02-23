<?php
class MySQL extends DBCore {

	private function _connect() {
		$config = Config::getInstance();
		$this->_link = new mysqli($config->dbHost, $config->dbUser, $config->dbPassword, $config->dbName, $config->dbPort);
		
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
	
	private function _q($sql) {
		return $this->_link->query($sql,MYSQLI_STORE_RESULT);
	}
	
	private function _escape($value) {
		return $this->_link->real_escape_string($value)
	}
	
	private function _rows() {
		return $this->_resultHandle->num_rows;
	}
	
	private function _affectedRows(){
		return $this->_link->affected_rows;
	}
	
	private function _insertId() {
		return $this->_link->insert_id;
	}	
	
	private function _errorNo() {
		return $this->_link->errno;
	}
	
	private function _errorMsg() {
		return $this->_link->error;	
	}
}

?>