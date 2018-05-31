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

namespace tigerduck\mmFramework\Api;

use \tigerduck\mmFramework as fw;

class PushOver
{
  private $_url     = 'https://api.pushover.net/1/messages.json';
  private $_token   = NULL;
  private $_userKey = NULL;

  public function __construct()
  {
    $config = fw\Config::getInstance();
    $this->_token   = $config->pushOverToken;
    $this->_userKey = $config->pushOverUserKey;
  }


  public function __get($name)
  {
    switch($name) {
      default:
        throw new Exception(__METHOD__ . " - Property " . $name . " not defined!");
        break;
    }
  }

  public function __set($name, $value)
  {
    switch($name) {
      default:
        throw new Exception(__METHOD__ . " - Property " . $name . " not defined!");
        break;
    }
  }

  public static function notify($message, $title = NULL)
  {

    $api = new self();
    $postData = array(
      'token'   => $api->_token,
      'user'    => $api->_userKey,
      'message' => $message,
      'html'    => 1,
    );

    if (!is_null($title)) {
      $postData['title'] = $title;
    }

    $jsonResponse = fw\Url::post($api->_url, $postData);
    if (0 < strlen($jsonResponse)) {
      $responseObj = fw\Json::decode($jsonResponse);

      if (1 !== $responseObj->status) {
        foreach ($responseObj->errors as $error) {
          trigger_error('PushOverApi Error: ' . $error);
        }
      }
    } else {
      trigger_error("PushOverApi Error: Got no response.");
    }
  }
}
