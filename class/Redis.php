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

class Redis
{

  private static $_useRedis = TRUE;

  private $_redis           = NULL;

  public function __construct($timeOut = 2)
  {
    $config = Config::getInstance();

    // Check if we should use redis
    if ($config->redisHost == 'none') {
      self::$_useRedis = FALSE;
    }

    if (self::$_useRedis) {
      try {
        $this->_redis = new \Redis();
        $success = $this->_redis->pconnect($config->redisHost, NULL, $timeOut);
        if (!$success) {
          throw new \RedisException("Can't connect to Redis server " . $config->redisHost);
        }

        // Select specific database
        if ($config->exists('redisDb')) {
          if (!$this->_redis->select($config->redisDb)) {
            throw new \RedisException("Can't select database " . $config->redisDb);
          }
        }
      } catch (\RedisException $ex) {
        self::$_useRedis = FALSE;
        // Don't use trigger_error here.
        // This will cause a infinite loop because we need redis for error handling as well.

        // But we send the message about redis just once an hour!!! To prevent to many emails!!!
        $redisBlock = sys_get_temp_dir() . '/redisBlock_' . md5(__FILE__);
        if (!file_exists($redisBlock) || (time() - filemtime($redisBlock)) > 3600) {
          softException($ex);
          file_put_contents($redisBlock, time());
        }
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

  /**
   * Pushes all method calls to the Redis Client instance
   */
  public function __call($method, $args)
  {
    if (!self::$_useRedis) {
      return NULL;
    }

    try {
      $reflectionMethod = new \ReflectionMethod($this->_redis, $method);
      return $reflectionMethod->invokeArgs($this->_redis, $args);
    } catch (\ReflectionException $ex) {
      echo_nice($ex->getMessage());
      //trigger_error($ex->getMessage());
    }
  }


  public function get($key)
  {
    if (!self::$_useRedis || !$this->_redis->exists($key)) {
      return NULL;
    }

    $type = $this->_redis->hget($key, 'type');
    $data = $this->_redis->hget($key, 'data');

    if ((FALSE === $type) || (FALSE === $data)) {
      //echo_nice("DELLLLLLLL");
      $this->_redis->del($key);
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

    $success1 = $this->_redis->hset($key, 'data', $data);
    $success2 = $this->_redis->hset($key, 'type', $type);
    $success = (FALSE !== $success1) & (FALSE !== $success2);
    if (!$success) {
      $this->_redis->del($key);
    }
    return (bool)$success;
  }

  /**
   * Flush redis keys
   * @param  string $redisKeyWildcard Redis key with optional; wildcard (*)
   * @return boolen                   success
   */
  public function flush($redisKeyWildcard)
  {
    if (!self::$_useRedis) {
      return NULL;
    }

    $success = TRUE;
    $keyList = $this->keys($redisKeyWildcard);
    foreach ($keyList as $redisKey) {
      $success &= $this->del($redisKey);
    }
    return $success;
  }
}
