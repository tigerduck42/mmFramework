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

class Lock
{

  private $_lockPath = '/tmp';
  private $_lockFile = NULL;

  public function __construct($key)
  {
    $lockFile = $this->_lockPath . "/" . $key . ".lock";
    if (!is_writable($this->_lockPath)) {
      throw new Exception(__METHOD__ . ' - Lock file ' . $lockFile . " is not writable!");
    }

    $this->_lockFile = $lockFile;
  }

  public function __get($name)
  {
    switch($name) {
      case "isRunning":
        return file_exists($this->_lockFile);
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

  public function set()
  {
    $success = FALSE;
    if (!file_exists($this->_lockFile)) {
      // No lock file found
      $success = $this->_writeLock();
    } else {
      $oldPid = file_get_contents($this->_lockFile);
      $processStack = $this->_getProcessStack();
      if ((0 < count($processStack)) && (!in_array($oldPid, $processStack))) {
        // not running
        $success = $this->_writeLock();
      }
    }
    return $success;
  }

  public function release()
  {
    if ($this->isRunning) {
      unlink($this->_lockFile);
    }
  }

  private function _writeLock()
  {
    $data = getmypid();
    $success = FALSE;
    $bytes = file_put_contents($this->_lockFile, $data);
    if ($bytes > 0) {
      $success = TRUE;
    }
    return $success;
  }

  private function _getProcessStack()
  {
    // Get the running process list
    $processStack = array();

    $cmd = "ps aux";
    exec($cmd, $output, $exitStatus);

    if (0 == $exitStatus) {
      foreach ($output as $line) {
        $parts = preg_split('{\s+}', $line);
        $runningPid = $parts[1];
        $processStack[] = $runningPid;
      }
    }
    return $processStack;
  }
}
