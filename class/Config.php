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

namespace mmFramework;

use mmFramework as fw;

class Config
{
  private static $_obj = NULL;
  private static $_validSections = array(
    'general',
    'db',
    'userdefined',
  );

  private $_timezone              = NULL;
  private $_gaCode                = NULL;
  private $_mailer                = NULL;
  private $_mailHostName          = NULL;
  private $_mailPort              = NULL;
  private $_mailSecure            = NULL;
  private $_mailUsername          = NULL;
  private $_mailPassword          = NULL;
  private $_mailSender            = NULL;

  private $_errorLog              = NULL;
  private $_errorEmail            = NULL;
  private $_mailOverRide          = NULL;

  private $_enableGA              = FALSE;
  private $_smartyForceRecompile  = FALSE;
  private $_isDevServer           = FALSE;
  private $_forceAssetLoad        = FALSE;
  private $_assertActive          = FALSE;

  // Helper stacks
  private $_configFileStack       = array();
  private $_reservedStack         = array();
  private $_userDefinedStack      = array();
  private $_databaseStack         = array();


  private function __construct()
  {
    $this->_configFileStack = array(
      DIR_BASE . '/../masterConfig.ini',
      DIR_BASE . '/init/config.ini',
      DIR_BASE . '/init/config_dev.ini',
    );

    // Can we find a dev config?
    if (file_exists(DIR_BASE . '/init/config_dev.ini')) {
      $this->_isDevServer = TRUE;
    }

    // get reserved keys
    $attributes = array_keys(get_object_vars($this));

    $reserved[] = "hostName";
    $reserved[] = "dbConnector";
    $reserved[] = "dbHost";
    $reserved[] = "dbPort";
    $reserved[] = "dbName";
    $reserved[] = "dbUser";
    $reserved[] = "dbPassword";
    $reserved[] = "dbCharset";

    foreach ($attributes as $attrib) {
      $attrib = preg_replace('{^_*}', '', $attrib);
      $reserved[] = $attrib;
    }
    $this->_reservedStack = $reserved;

    // Init default db settings
    $dbSetting =
    $this->_databaseStack['default'] = array (
      'dbConnector' => 'mysql',
      'dbHost'      => 'localhost',
      'dbPort'      => 3306,
      'dbName'      => NULL,
      'dbUser'      => NULL,
      'dbPassword'  => NULL,
      'dbCharset'   => 'utf8',
    );

    $this->_parseConfigs();

    //
    // Set assert activity
    //

    if ($this->assertActive == TRUE) {
      assert_options(ASSERT_ACTIVE, 1);
    } else {
      assert_options(ASSERT_ACTIVE, 0);
    }
  }

  private function _parseConfigs()
  {
    $parsedFiles = 0;
    foreach ($this->_configFileStack as $configFile) {
      if (!file_exists($configFile)) {
        continue;
      }
      $section = NULL;
      $parsedFiles++;
      $config = file_get_contents($configFile);
      $match = array();

      $lines = explode("\n", $config);

      foreach ($lines as $line) {

        if (preg_match('{\[([^:]+)\:?([^\]]+)?\]}', $line, $match)) {
          // Get sections ...
          $section = trim($match[1]);

          $subsection = NULL;
          if (isset($match[2])) {
            $subsection = trim($match[2]);
          }
        } else if (preg_match('{(.+)=(.+)}', $line, $match)) {

          // Check if we have a valid section
          if (is_null($section)) {
            throw new Exception(__METHOD__ . " - No config section found!");
          }

          if (!in_array($section, self::$_validSections)) {
            throw new Exception(__METHOD__ . " - " . $section . " is not a valid section!");
          }

          // Get config ...
          $key = trim($match[1]);
          $value = trim($match[2]);

          // Skip comments
          if (preg_match('/^\s*;/', $key)) {
            continue;
          }

          switch($section) {
            case 'general':
              $privateKey = '_' . $key;
              $this->$privateKey = $value;
              break;
            case 'db':
              if (!is_null($subsection)) {
                if (!isset($this->_databaseStack[$subsection])) {
                  $this->_databaseStack[$subsection] = $this->_databaseStack['default'];
                }
                $this->_databaseStack[$subsection][$key] =  $value;
              } else {
                $this->_databaseStack['default'][$key] =  $value;
              }
              break;
            case 'userdefined':
              if (in_array($key, $this->_reservedStack)) {
                trigger_error("User defined parameter '" . $key . "' is reserved (" . $configFile . ")", E_USER_WARNING);
              } else {
                $this->_userDefinedStack[$key] = $value;
              }
              break;
          }
        }
      }
    }

    if (0 == $parsedFiles) {
      throw new \exception("Can't read any config file ('" . implode("', '", $this->_configFileStack) . "')");
    }
  }

  public static function getInstance()
  {
    if (is_null(self::$_obj)) {
      $className = __CLASS__;
      self::$_obj = new $className();
    }
    return self::$_obj;
  }

  public function __get($name)
  {
    switch($name) {
      case 'timezone':
        return $this->_timezone;
        break;
      case 'isDevServer':
        return $this->_fixBoolean($this->_isDevServer);
        break;
      case 'smartyForceRecompile':
        return $this->_fixBoolean($this->_smartyForceRecompile);
        break;
      case "enableGA":
        return $this->_fixBoolean($this->_enableGA);
        break;
      case "assertActive":
        return $this->_fixBoolean($this->_assertActive);
        break;
      case "gaCode":
        return $this->_gaCode;
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
        return HTTP::hostname();
        break;
      case 'errorLog':
        return $this->_errorLog;
        break;
      case 'forceAssetLoad':
        return $this->_forceAssetLoad;
        break;
      case 'errorEmail':
        if (is_null($this->_errorEmail) || !fw\MyMailer::ValidateAddress($this->_errorEmail)) {
          throw new Exception(__METHOD__ . " - Error email not defined!");
        }
        return $this->_errorEmail;
        break;
      case 'mailOverRide':
        if (!is_null($this->_mailOverRide) && !fw\MyMailer::ValidateAddress($this->_errorEmail)) {
          throw new Exception(__METHOD__ . " - Error email not defined!");
        }
        return $this->_mailOverRide;
        break;
      case 'dbConfiguration':
        return $this->_databaseStack;
        break;
      default:
        if (isset($this->_userDefinedStack[$name])) {
          $value = $this->_userDefinedStack[$name];
          if (0 === strcasecmp($value, 'true')) {
            return TRUE;
          } else if (0 === strcasecmp($value, 'false')) {
            return FALSE;
          } else {
            return $value;
          }
        } else {
          trigger_error(__CLASS__ . "::Getter - Attribute " . $name . " not defined!", E_USER_ERROR);
        }
        break;
    }
  }

  private function _fixBoolean ($check)
  {
    if (($check > 0) || (0 === strcasecmp($check, 'true'))) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

  public function exists($name)
  {
    // Private attributes
    if (property_exists($this, '_' . $name)) {
      return TRUE;
    }

    // User defined attributes
    if (isset($this->_userDefinedStack[$name])) {
      return TRUE;
    }

    // nothing found
    return FALSE;
  }

  public function __set($name, $value)
  {
    trigger_error(__CLASS__ . "::Setter - '" . $name . "' Please use config file!", E_USER_ERROR);
  }

  public function __clone()
  {
    trigger_error('Clone is not allowed.', E_USER_ERROR);
  }
}
