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

namespace mmFramework;

class Logger
{
  const LOG_CONSOLE = 1;
  const LOG_FILE    = 2;
  const LOG_DB      = 4;
  const LOG_MAIL    = 8;
  const LOG_RETURN  = 16;

  const TS_FORMAT_LOG = 'log';

  private $_withTimestamp  = FALSE;
  private $_handleType     = 1;

  // Mail settings
  private $_maxRetry       = 1;
  private $_fromName       = 'Logger';
  private $_fromAddress    = NULL;
  private $_toAddress      = NULL;

  // Digest settings
  private $_digestStack    = array();
  private $_digestSubject  = NULL;

  // File settings
  private $_fileName       = NULL;

  private $_styleStack = array(
    'red'     => "\e[91m",
    'green'   => "\e[92m",
    'blue'    => "\e[94m",
    'magenta' => "\e[95m",

    'bold'    => "\e[1m",
    'invers'  => "\e[7m",

    'reset'   => "\e[0m",
  );



  public function __construct($type = self::LOG_CONSOLE)
  {
    $this->_handleType = $type;
  }

  public function __destruct()
  {
    if (0 < count($this->_digestStack)) {
      $body = "";
      foreach ($this->_digestStack as $style => $lines) {
        $block = implode("<br/>", $lines);

        if (preg_match('{^([^:]+)::}', $style, $match)) {
          $tag = $match[1];
          $addStyle = '';
          if ($tag == 'pre') {
            $addStyle = ' style="font-size: 0.9em;"';
          }
          $body .= '<' . $tag . $addStyle . '>' . $block . '</' . $tag . '>';
        } else {
          $body .= $block;
        }
        $body .= "<br/>";
      }

      $subject = "[digest] " . $this->_digestSubject;
      $this->_mail($body, $subject);
    }
  }

  private function _setupMail()
  {
    if (is_null($this->_toAddress)) {
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
      case 'digestSubject':
        $this->_digestSubject = $value;
        break;
      default:
        throw new Exception(__METHOD__ . " - Property " . $name . " not defined!", 2);
        break;
    }
  }


  public function mail($msg)
  {
    $oldType       = $this->_handleType;
    $oldTSHandling = $this->_withTimestamp;

    $this->_handleType    = self::LOG_MAIL;
    $this->_withTimestamp = FALSE;

    $this->write($msg);

    $this->_handleType    = $oldType;
    $this->_withTimestamp = $oldTSHandling;
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

  public function digest($msg, $digestKey = 'default')
  {
    if (!isset($this->_digestStack[$digestKey])) {
      $this->_digestStack[$digestKey] = array();
    }
    $this->_digestStack[$digestKey][] = $msg;
  }

  private function _build($msg, $format = NULL)
  {
    // remove html entities
    $msg = html_entity_decode($msg);

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
      $date = new \DateTime('now');
      if (TRUE === $this->_withTimestamp) {
        $msg = '[' . $date->format('r') . '] - ' . $msg;
      } else {
        switch($this->_withTimestamp) {
          case self::TS_FORMAT_LOG:
            $this->_withTimestamp = 'D j M H:i:s T Y';
            break;
        }

        $msg = '[' . $date->format($this->_withTimestamp) . '] - ' . $msg;
      }
    }

    return $msg;
  }

  private function _mail($msg, $subject = NULL)
  {
    $this->_setupMail();

    $mail = new MyMailer();
    $mail->From = $this->_fromAddress;
    $mail->FromName = $this->_fromName;
    $mail->AddAddress($this->_toAddress);
    if (is_null($subject)) {
      $mail->Subject = $msg;
    } else {
      $mail->Subject = $subject;
    }
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
