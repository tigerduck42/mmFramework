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

class HTTP
{

  /*
   * GET
   */

  public static function get($name)
  {
    if (isset($_GET[$name])) {
      return $_GET[$name];
    } else {
      return NULL;
    }
  }

  /*
   * POST
   */

  public static function post($name)
  {
    if (isset($_POST[$name])) {
      return $_POST[$name];
    } else {
      return NULL;
    }
  }

  /*
   * Server
   */

  public static function server($name)
  {
    if (isset($_SERVER[$name])) {
      return $_SERVER[$name];
    } else {
      return NULL;
    }
  }



  /*
   * Cookie
   */
  public static function setCookie($name, $value, $expire = 0)
  {
    if (!setcookie($name, $value, $expire)) {
      trigger_error("Cookie " . $name . " could not be set!", E_USER_ERROR);
    }
  }

  public static function getCookie($name)
  {
    $cookie = NULL;
    if (self::isCookieSet($name)) {
      $cookie = $_COOKIE[$name];
    }

    return $cookie;
  }

  public static function deleteCookie($name)
  {
    self::setCookie($name, "", time() - 3600);
  }


  public static function isCookieSet($name)
  {
    return isset($_COOKIE[$name]);
  }

  /*
   * Session
   */

  public static function setSession($name, $value)
  {
    $_SESSION[$name] = $value;
  }

  public static function getSession($name)
  {
    $value = NULL;
    if (self::isSessionSet($name)) {
      $value = $_SESSION[$name];
    }

    return $value;
  }

  public static function isSessionSet($name)
  {
    return isset($_SESSION[$name]);
  }

  /*
   * Misc
   */
  public static function me()
  {
    return $_SERVER['SCRIPT_NAME'];
  }

  public static function refresh()
  {
    self::redirect(self::me());
    exit;
  }

  public static function redirect($url)
  {
    header("Location: " . $url);
    exit;
  }

  public static function hostname()
  {
    $hostname = "Unknown host";

    $tryStack = array(
      'HOSTNAME',
      'SERVER_NAME',
      'HTTP_HOST',
    );
    foreach ($tryStack as $item) {
      $name = HTTP::server($item);
      if (!is_null($name)) {
        $hostname = " - " . $name;
        break;
      }
    }

    return $hostname;
  }
}
