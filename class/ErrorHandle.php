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

class ErrorHandle
{

  const WEB  = 1;
  const MAIL = 2;
  const CLI  = 4;
  const LOG  = 8;

  public static $mask = 0;

  private static $_errorCount = 0;
  private static $_stylesInjected = FALSE;

  private $_scope = NULL;
  private $_no = NULL;
  private $_string = NULL;
  private $_file = NULL;
  private $_line = NULL;
  private $_context = NULL;
  private $_mailTo = NULL;

  public function __construct($scope = self::WEB)
  {
    $config = Config::getInstance();
    $this->_scope = $scope;
    $this->_mailTo = $config->errorEmail;
    self::$_errorCount++;
  }


  public function __set($name, $value)
  {
    switch($name) {
      case 'no':
        $this->_no = $value;
        break;
      case 'string':
        $this->_string = $value;
        break;
      case 'file':
        $this->_file = $value;
        break;
      case 'line':
        $this->_line = $value;
        break;
      case 'context':
        $this->_context = $value;
        break;
      case 'mailTo':
        $this->_mailTo = $value;
        break;
      default:
        throw new Exception(__CLASS__ . "::Setter: Attribute " . $name . " not defined!");
        break;
    }
  }


  public function output()
  {
    // Skip error/warning if flagging mask is set
    if ($this->_no & self::$mask) {
      return;
    }

    if ($this->_scope & self::WEB) {
      echo $this->_outputWeb();
    }

    if ($this->_scope & self::CLI) {
      echo $this->_outputCli();
    }

    if ($this->_scope & self::MAIL) {
      $this->_outputMail();
    }

    if ($this->_scope & self::LOG) {
      $this->_outputLog();
    }
  }

  private function _outputWeb()
  {
    if (!self::$_stylesInjected) {
      echo $this->_injectStyle();
      self::$_stylesInjected = TRUE;
    }
    echo $this->_buildMessageBox(FALSE);
  }

  private function _outputCli($return = FALSE)
  {
    $msg = $this->_buildMessageBox(FALSE);
    $msg = preg_replace('{<br\/?\>}', "\n", $msg);
    $msg = strip_tags($msg);

    $pad = str_pad("\n", 150, "-", STR_PAD_LEFT);

    if ($return) {
      $msg = $pad . $msg;
      return $msg;
    } else {
      $msg = $pad . "Time: " . date('r') . "\n" . $msg . $pad;
      echo $msg;
    }
  }

  private function _outputLog()
  {
    // get config
    $config = Config::getInstance();

    // Nothing to do
    if (is_null($config->errorLog)) {
      return;
    }

    $info = pathinfo($config->errorLog);
    if (is_writeable($info['dirname'])) {
      $msg = $this->_outputCli(TRUE);

      // add date and time in front of the line
      $lines = explode("\n", $msg);
      $msg = '';
      foreach ($lines as $line) {
        if (strlen($line) > 0) {
          $msg .= "[" . date('r') . "] - " . $line . "\n";
        }
      }

      $fp = fopen($config->errorLog, 'a');
      fputs($fp, $msg);
      fclose($fp);
    } else {
      throw new Exception(__METHOD__ . " - Can't write to log file " . $config->errorLog);
    }
  }

  private function _outputMail()
  {
    $body  = $this->_injectStyle();
    $body .= $this->_buildMessageBox(TRUE);
    echo $this->_send($body);
  }

  public function send($msg)
  {
    echo $this->_send("<p>" . $msg . "</p>");
  }

  private function _send($msg)
  {
    $mail = new MyMailer();
    $mail->From = $this->_mailTo;
    $mail->FromName = "WEBError";
    $mail->AddAddress($this->_mailTo, "WebAdmin");
    $mail->Subject = "";
    if (self::$_errorCount == 10) {
      $mail->Subject .= "[BLOCKED] ";
    }
    $mail->Subject .= "Error on " . HTTP::hostname();
    $mail->IsHTML();
    $mail->setBody($msg);

    if (self::$_errorCount <=  10) {
      if (!$mail->Send()) {
        return $mail->ErrorInfo . "<br/>";
      }
    }
  }

  private function _injectStyle()
  {
    $style = '
      <style type="text/css">
        .__error__ {
          margin: 20px 20px;
          clear: both;
          overflow: hidden;
          border: 1px solid;
          padding: 10px;
          background-color: #ff6666;
          color: #000000;
          font-size: 12px;
          z-index: 1000000;
          position: relative;
        }

         strong {
          font-weight: bold;
        }
      </style>
      ';
    return $style;
  }

  private function _buildMessageBox($addContext)
  {
    $html = '';
    $html .= '<div class="__error__">';
    $html .= '<strong>Time:</strong> ' . date('r') . '<br/>';
    $html .= '<strong>Error:</strong> ' . $this->_string . '<br/>';
    $html .= '<strong>File:</strong> ' . $this->_file  . ' (' . $this->_line  . ')<br/>';

    $stackCore = array_reverse(debug_backtrace());

    $stack = array();
    foreach ($stackCore as $stackData) {
      if (isset($stackData['args'])) {
        unset($stackData['args']);
      }
      if (isset($stackData['object'])) {
        unset($stackData['object']);
      }

      $stack[] = $stackData;
    }

    $btHtml = "";
    foreach ($stack as $depth => $rec) {
      if ($rec['function'] == 'customError') {
        break;
      }

      $btHtml .= $depth . ': ';
      if (isset($rec['class'])) {
        $btHtml .= $rec['class'] . '::';
      }
      $btHtml .= $rec['function'] . '(';
      if (isset($rec['args'])) {
        $btHtml .= implode(',', $rec['args']);
      }
      $btHtml .= ')';

      if (isset($rec['file']) && isset($rec['line'])) {
        $btHtml .= ', at ' . $rec['file'] . ' line ' . $rec['line'] . '<br/>';
      }
    }

    if (strlen($btHtml)) {
      $html .= '<br/><b>Backtrace:</b><br/>';
      $html .= $btHtml;
    }

    //if ($addContext && count($stack)) {
    //  $html .= '<br/><b>Stack</b>';
    //  $html .= '<pre>' . print_r($stack, TRUE) . '</pre>';
    //}

    if ($addContext && count($this->_context)) {
      $html .= '<br/><b>Context</b>';
      $html .= '<pre>' . print_r($this->_context, TRUE) . '</pre>';
    }

    $html .= "</div>\n";

    return $html;
  }

  public static function disable($mask)
  {
    self::$mask = self::$mask | $mask;
  }

  public static function reenable($mask = NULL)
  {
    if (is_null($mask)) {
      self::$mask = 0;
    } else {
      self::$mask = self::$mask & ~$mask;
    }
  }

  public static function getErrorCode($prefix, $id)
  {
    $constants = get_defined_constants();
    $reverse = array();
    foreach ($constants as $key => $value) {
      if (preg_match('{^' . $prefix . '}', $key)) {
        $reverse[$value] = $key;
      }
    }

    if (isset($reverse[$id])) {
      return $reverse[$id];
    } else {
      return '_ERROR_UNKNOWN_';
    }
  }
}
