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

class Logger
{

  const LOG_CONSOLE = 1;
  const LOG_FILE    = 2;
  const LOG_DB      = 4;
  const LOG_MAIL    = 8;
  const LOG_RETURN  = 16;

  private $_withTimestamp  = FALSE;
  private $_handleType     = 1;

  // Mail settings
  private $_maxRetry       = 1;
  private $_fromName       = 'Logger';
  private $_fromAddress    = NULL;
  private $_toAddress      = NULL;

  // File settings
  private $_fileName       = NULL;

  private $_styleStack = array(
    'reset' => "\e[0m",

    'red'   => "\e[91m",
    'green' => "\e[92m",

    'bold'  => "\e[1m",
  );



  public function __construct($type = self::LOG_CONSOLE)
  {
    $this->_handleType = $type;

    // Set defaults for Mail
    if (self::LOG_MAIL & $this->_handleType) {
      $config = Config::getInstance();
      $this->_toAddress   = $config->errorEmail;
      $this->_fromAddress = $config->errorEmail;
    }
  }

  public function __get($name)
  {
    switch($name) {
      default:
        throw new Exception(__METHOD__ . " - Property " . $name . " not defined!", 2);
        break;
    }
  }

  public function __set($name, $value)
  {
    switch($name) {
      case 'withTimestamp':
        $this->_withTimestamp = $value;
        break;
      case 'handleType':
        $this->_handleType = $value;
        break;
      case 'fileName':
        $this->_fileName = $value;
        break;
      default:
        throw new Exception(__METHOD__ . " - Property " . $name . " not defined!", 2);
        break;
    }
  }

  public function write($msg, $format = NULL)
  {

    $logMsg = $this->_build($msg, $format);

    if (self::LOG_CONSOLE & $this->_handleType) {
      echo $logMsg . "\n";
    }

    if (self::LOG_FILE & $this->_handleType) {
      if (is_null($this->_fileName)) {
        trigger_error(__METHOD__ . " - no log file name defined!");
      } else {
        $fp = fopen($this->_fileName, "a");
        if ($fp !== FALSE) {
          fputs($fp, $logMsg . "\n");
          fclose($fp);
        }
      }
    }

    if (self::LOG_DB & $this->_handleType) {
      trigger_error(__METHOD__ . " - LOG_DB: Not implemented so far");
    }

    if (self::LOG_MAIL & $this->_handleType) {
      self::_mail($logMsg);
    }

    if (self::LOG_RETURN & $this->_handleType) {
      return $logMsg;
    }
  }

  private function _build($msg, $format = NULL)
  {
    if (!is_null($format)) {
      $parts = explode("|", $format);
      $styledMsg = '';
      foreach ($parts as $style) {
        if (isset($this->_styleStack[$style])) {
          $styledMsg .= $this->_styleStack[$style];
        }
      }
      $styledMsg .= $msg;
      $styledMsg .= $this->_styleStack['reset'];
      $msg = $styledMsg;
    }

    if ($this->_withTimestamp) {
      $msg = '[' . date('r') . '] - ' . $msg;
    }

    return $msg;
  }

  private function _mail($msg)
  {
    $mail = new MyMailer();
    $mail->From = $this->_fromAddress;
    $mail->FromName = $this->_fromName;
    $mail->AddAddress($this->_toAddress);
    $mail->Subject = $msg;
    $mail->IsHTML();
    $mail->setBody("<p>" . $msg . "</p>");

    $successSent = FALSE;
    $i = $this->_maxRetry;
    while (!$successSent && $i>0) {
      $successSent = $mail->Send();
      sleep(2);
      $i--;
    }
  }
}
