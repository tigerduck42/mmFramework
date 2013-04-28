<?php

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
      $fp = fopen($logName, "a");
      fputs($fp, $logMsg . "\n");
      fclose($fp);
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