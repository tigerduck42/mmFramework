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

class Json
{
  public static function decode($data, $forceArray = FALSE)
  {
    $obj = json_decode($data, $forceArray);

    $jsonErrCode = json_last_error();
    if ($jsonErrCode > 0) {
      throw new Exception(__METHOD__ . " - Json Error: " .  ErrorHandle::getErrorCode('JSON_ERR', $jsonErrCode));
      exit;
    }

    return $obj;
  }

  public static function encode($value)
  {
    $jsonData = json_encode($value);

    $jsonErrCode = json_last_error();
    if ($jsonErrCode > 0) {
      throw new Exception(__METHOD__ . " - Json Error: " .  ErrorHandle::getErrorCode('JSON_ERR', $jsonErrCode));
      exit;
    }

    return $jsonData;
  }
}
