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
namespace mmFramework\DB;

abstract class Helper
{
  protected $_logger    = NULL;
  protected $_changeStack = array();

  protected $_isDirty = TRUE;

  abstract protected function _load($id);
  abstract protected function _loadByRow($row);


  protected function _assign($name, $value)
  {
    $privateName = "_" . $name;

    if ($this->$privateName !== $value) {

      // logging enabled .. register change
      if (is_object($this->_logger)) {
        $this->_changeStack[$name] = array(
          'old' =>  $this->$privateName,
          'new' => $value,
        );
      }

      $this->$privateName = $value;
      $this->_isDirty = TRUE;
    }
  }

  protected function _postSave()
  {
    assert(is_object($this->_logger));

    if (is_object($this->_logger)) {
      // Fix logger action
      if (is_null($this->_logger->action)) {
        $this->_logger->action = get_class($this);
      }

      if (0 < count($this->_changeStack)) {
        $old = array();
        $new = array();

        foreach ($this->_changeStack as $name => $rec) {
          $old[] = $name . ": " . $rec['old'];
          $new[] = $name . ": " . $rec['new'];
        }

        $this->_logger->write(implode("\n", $old), implode("\n", $new));
      }
    }
  }
}
