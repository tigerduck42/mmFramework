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

class Url
{

  const METHOD_GET  = 1;
  const METHOD_POST = 2;

  public static function get($url)
  {
    return self::client($url, self::METHOD_GET);
  }

  public static function post($url, $data)
  {
    return self::client($url, self::METHOD_POST, $data);
  }


  private static function client($url, $method = self::METHOD_GET, $data = NULL)
  {

    if (($method == self::METHOD_POST) && empty($data)) {
      trigger_error(__CLASS__ . ":: Nil POST data supplied.", E_USER_ERROR);
      return NULL;
    }

    $requestHeaders = array();
    //$requestHeaders[] = "Content-type:    application/x-www-form-urlencoded; charset=UTF-8";
    //$requestHeaders[] = "Content-Length: 23";
    //$requestHeaders[] = "SOAPAction: http://services.xpl.com.au/Host/Provider/IProviderService/" . $action;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_TIMEOUT, 80);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);

    switch($method) {
      case self::METHOD_POST:
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        break;
      default:
        curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
        break;
    }

    //curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/mm');
     //curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/mm');

    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.1; rv:31.0) Gecko/20100101 Firefox/31.0');
    if (count($requestHeaders) > 0) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
    }

    if (FALSE === ($response = curl_exec($ch))) {
        $errNo = curl_errno($ch);
        $errMsg = curl_error($ch);
        trigger_error(__CLASS__ . "::cUrl Error (" . $errNo. ") " .$errMsg, E_USER_ERROR);
        $response = NULL;
    }

    return $response;
  }
}
