<?php
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