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

use mmFramework as fw;

class Redis extends \Redis
{

  private static $_useRedis = TRUE;

  public function __construct($timeOut = 2)
  {
    $config = Config::getInstance();

    if (self::$_useRedis) {
      try {
        parent::__construct();
        $success = $this->pconnect($config->redisHost, NULL, $timeOut);
        if (!$success) {
          throw new \RedisException("Can't connect to Redis server " . $config->redisHost);
        }

        // Select specific database
        if ($config->exists('redisDb')) {
          if (!parent::select($config->redisDb)) {
            throw new \RedisException("Can't select database " . $config->redisDb);
          }
        }

      } catch (\RedisException $ex) {
        self::$_useRedis = FALSE;
        //trigger_error($ex->getMessage(), E_USER_ERROR);
      }
    }
  }

  public function __get($name)
  {
    switch($name) {
      case "useRedis":
        return self::$_useRedis;
        break;
      default:
        throw new Exception(__METHOD__ . " - Parameter " . $name . " not defined!");
        break;
    }
  }


  public function get($key)
  {
    if (!self::$_useRedis || !parent::exists($key)) {
      return NULL;
    }

    $type = parent::hget($key, 'type');
    $data = parent::hget($key, 'data');

    if ((FALSE === $type) || (FALSE === $data)) {
      //echo_nice("DELLLLLLLL");
      parent::del($key);
      return NULL;
    }

    switch($type) {
      case 'serialize|object':
      case 'serialize|array':
        $data = unserialize($data);
        break;
      case 'json|object':
        $data = fw\Json::decode($data);
        break;
      case 'json|array':
        $data = fw\Json::decode($data, TRUE);
        break;
    }

    return $data;
  }

  public function set($key, $data)
  {
    if (!self::$_useRedis) {
      return FALSE;
    }

    $type = 'string';
    switch(TRUE) {
      case is_array($data):
        $data = fw\Json::encode($data);
        $type = 'json|array';
        break;
      case is_object($data):
        $data = serialize($data);
        $type = 'serialize|object';
        break;
    }

    $success1 = parent::hset($key, 'data', $data);
    $success2 = parent::hset($key, 'type', $type);
    $success = (FALSE !== $success1) & (FALSE !== $success2);
    if (!$success) {
      parent::del($key);
    }
    return (bool)$success;
  }

  public function del($key)
  {
    if (self::$_useRedis) {
      return parent::del($key);
    }
    return 0;
  }

  public function expire($key, $lease)
  {
    if (self::$_useRedis) {
      return parent::expire($key, $lease);
    }
  }
}
