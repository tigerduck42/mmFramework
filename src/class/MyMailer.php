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

namespace tigerduck42\mmFramework;

class MyMailer extends \PHPMailer
{

  private $_preBody = '';
  private $_overrideAddress = NULL;

  public function __construct($exceptions = FALSE)
  {
    parent::__construct($exceptions);

    $config = Config::getInstance();

    switch ($config->mailer) {
      case 'smtp':
        $this->IsSMTP();
        $this->Host = $config->mailHostName;
        if (strlen($config->mailPort)) {
          $this->Port = $config->mailPort;
        }
        if (strlen($config->mailUsername) && strlen($config->mailPassword)) {
          $this->SMTPAuth = TRUE;
        } else {
          $this->SMTPAuth = FALSE;
        }
        if (strlen($config->mailSecure)) {
          $this->SMTPSecure = $config->mailSecure;
        }
        $this->Username = $config->mailUsername;
        $this->Password = $config->mailPassword;
        break;
      case 'sendmail':
        $this->IsSendmail();
        break;
      default:
        $this->IsMail();
        break;
    }

    if (!is_null($config->mailSender) && self::ValidateAddress($config->mailSender)) {
      $this->Sender = $config->mailSender;
    }

    $this->CharSet = "utf-8";

    if (!is_null($config->mailOverRide)) {
      if (self::ValidateAddress($config->mailOverRide)) {
        $this->_overrideAddress = $config->mailOverRide;
      } else {
        trigger_error("No valid override address given!", E_USER_ERROR);
      }
    }
  }

  public function addRecipients($recipientArray)
  {
    if (!is_array($recipientArray)) {
      trigger_error("Recipients not defined!", E_USER_ERROR);
      return FALSE;
    }

    if (!isset($recipientArray['TO'])) {
      trigger_error("No TO recipients defined!", E_USER_ERROR);
      return FALSE;
    }

    foreach ($recipientArray['TO'] as $email => $name) {
      $this->AddAddress($email, $name);
    }

    if (isset($recipientArray['CC'])) {
      foreach ($recipientArray['CC'] as $email => $name) {
        $this->AddCC($email, $name);
      }
    }

    if (isset($recipientArray['BCC'])) {
      foreach ($recipientArray['BCC'] as $email => $name) {
        $this->AddBCC($email, $name);
      }
    }
  }

  protected function AddAnAddress($tag, $address, $name = "")
  {
    $config = Config::getInstance();

    // Never override error email address
    if (strcmp($address, $config->errorEmail) != 0) {
      if (!is_null($this->_overrideAddress)) {
        if (strcmp($address, $this->_overrideAddress) != 0) {
          $this->_preBody .= "Override " . $address . " with " . $this->_overrideAddress . "\n";
        }
        $address = $this->_overrideAddress;
      }
    }

    parent::AddAnAddress($tag, $address, $name);
  }

  public function setBody($body)
  {
    $this->Body = nl2br($this->_preBody) . $body;
  }

  public function setAltBody($body)
  {
    $this->AltBody = $this->_preBody . $body;
  }

  public function send()
  {
    $config = Config::getInstance();
    $success = TRUE;
    if ($config->hasMailConfigured) {
      $success = parent::Send();

      if (!$success) {
        trigger_error("Mail could not be send!\n" . $this->ErrorInfo, E_USER_ERROR);
      }
    }
    return $success;
  }
}
