<?php


class Config {
	
	private static $_obj = NULL;
	private $_configFile = NULL;
	private $_timezone = NULL;
	private $_enableGA = FALSE;
	private $_smartyForceRecompile = FALSE;
	private $_isDevServer = FALSE;
	private $_dbConnector = 'mysql';
	private $_dbHost = 'localhost';
	private $_dbPort = 3306;
	private $_dbName = NULL;
	private $_dbUser = NULL;
	private $_dbPassword = NULL;
	private $_mailer = NULL;
	private $_mailHostName = NULL;
	private $_mailPort = NULL;
	private $_mailSecure = NULL;
	private $_mailUsername = NULL;
	private $_mailPassword = NULL;
	private $_mailSender = NULL;
	private $_hostName = NULL;
	private $_userDefined = array();
	

	private function __construct() {
		$this->_configFile = DIR_BASE . '/init/config.ini';
		
		// Can we find a dev config?
		if(file_exists(DIR_BASE . '/init/config_dev.ini')) {
			$this->_isDevServer = TRUE;
			$this->_configFile = DIR_BASE . '/init/config_dev.ini';	
		}
	
		$config = file_get_contents($this->_configFile);
		$match = array();
		if(preg_match_all('/(.+?)=(.+?)\n/', $config, $match, PREG_SET_ORDER)) {
			foreach($match as $m) {
				$key = trim($m[1]);
				$value = trim($m[2]);
				
				// Skip comments
				if(preg_match('/^[^;]/',$key)) {
					// Userdefinded settings
					if(preg_match("{user_(.+)}", $key,$match2)) {
						$key = $match2[1];
						$this->_userDefined[$key] = $value;
					} 
					else {
						$key = '_' . $key; 
						$this->$key = $value;
					}
				}
			}
		}
		else {
			throw new exception("Can't read config file " . $this->_configFile);
		}
	}
	
	public static function getInstance() {
		if(is_null(self::$_obj)) {
			$className = __CLASS__;
			self::$_obj = new $className();
		}
		return self::$_obj;
	}
	
	public function __get($name) {
		switch($name) {
			case 'isDevServer':
				$check = $this->_isDevServer;
				if(($check > 0) || (0 === strcasecmp($check, 'true'))) {
					return TRUE;
				}
				else {
					return FALSE;
				}
				break;
			case 'timezone' :
				return $this->_timezone;
				break;
			case 'smartyForceRecompile':
				$check = $this->_smartyForceRecompile;
				if(($check > 0) || (0 === strcasecmp($check, 'true'))) {
					return TRUE;
				}
				else {
					return FALSE;
				}
				break;
			case "enableGA":
				$check = $this->_enableGA;
				if(($check > 0) || (0 === strcasecmp($check, 'true'))) {
					return TRUE;
				}
				else {
					return FALSE;
				}
				break;
			case 'dbConnector': 
				return $this->_dbConnector; 
				break;
			case 'dbHost': 
				return $this->_dbHost; 
				break;
			case 'dbPort': 
				return $this->_dbPort; 
				break;
			case 'dbUser': 
				return $this->_dbUser; 
				break;
			case 'dbPassword': 
				return $this->_dbPassword; 
				break;
			case 'dbName': 
				return $this->_dbName; 
				break;
			case 'mailer': 
				return $this->_mailer; 
				break;
			case 'mailHostName': 
				return $this->_mailHostName; 
				break;
			case 'mailPort': 
				return $this->_mailPort; 
				break;
			case 'mailSecure': 
				return $this->_mailSecure; 
				break;
			case 'mailUsername': 
				return $this->_mailUsername; 
				break;
			case 'mailPassword': 
				return $this->_mailPassword; 
				break;
			case 'mailSender': 
				return $this->_mailSender; 
				break;

			case 'hostName': 
				return $this->_hostName; 
				break;	
			default:
				if(isset($this->_userDefined[$name])) {
					$value = $this->_userDefined[$name];
					if(0 === strcasecmp($value, 'true')){
						return TRUE;
					}
					else if(0 === strcasecmp($value, 'false')){
						return FALSE;
					}
					else {
						return $value;
					}
				}
				else {
					trigger_error(__CLASS__ . "::Getter - Attribute " . $name . " not defined!", E_USER_ERROR);
				}
				break;
		}
	}
	
	public function __set($name, $value) {
		trigger_error(__CLASS__ . "::Setter - '" . $name . "' Please use config file!", E_USER_ERROR);
	}
	
	public function __clone() {
		trigger_error('Clone is not allowed.', E_USER_ERROR);
   }

   public function __wakeup()
   {
		trigger_error('Unserializing is not allowed.', E_USER_ERROR);
   }
}

?>
