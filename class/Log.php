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

class Log {

  const LOG_CONSOLE = 1;
  const LOG_FILE = 2;
  const LOG_DB = 4;
  const LOG_MAIL = 8;

  private static $_withTimestamp = FALSE;
  private static $_handleType = 1;

  public function __construct($wTs){
    self::$_withTimestamp = $wTs;
  }

  public static function write($msg) {

    $logMsg = self::_build($msg);
    if(self::LOG_CONSOLE & self::$_handleType) {
      echo $logMsg . "\n";
    }

    if(self::LOG_FILE & self::$_handleType) {
      trigger_error(__METHOD__ . " - LOG_FILE: Not implemented so far");
     /*
      $fp = fopen($logName, "a");
      fputs($fp, $logMsg . "\n");
      fclose($fp);
    */
    }

    if(self::LOG_DB & self::$_handleType) {
      trigger_error(__METHOD__ . " - LOG_DB: Not implemented so far");
    }

    if(self::LOG_MAIL & self::$_handleType) {
      self::_mail($logMsg);
    }
  }

  public static function mail($msg) {
    $logMsg = self::_build($msg);
    self::_mail($logMsg);
  }


  private static function _build($msg) {
    $logMsg = '';
    if(self::$_withTimestamp) {
      $logMsg = '[' . date('r') . '] - ';
    }

    $logMsg .= $msg;
    return $logMsg;
  }

  private static function _mail($msg) {
    $config = Config::getInstance();

    $mail = new MyMailer();
    $mail->From = $config->otrUsername;
    $mail->FromName = "AppLog";
    $mail->AddAddress($config->otrUsername);
    $mail->Subject = $msg;
    $mail->IsHTML();
    $mail->Body = "<p>" . $msg . "</p>";

    if(!$mail->Send()) {
      return $mail->ErrorInfo . "<br/>";
    }
  }
}

?>