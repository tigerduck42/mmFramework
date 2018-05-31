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

namespace tigerduck\mmFramework;
ß
class GetOpt
{

  protected $_optStack = array();
  protected $_theStack = array();
  protected $_optStackKeys = array();

  public function __get($name)
  {
    switch($name) {
      case 'list':
        return array_keys($this->_theStack);
        break;
      default:
        if (isset($this->_theStack[$name])) {
          return $this->_theStack[$name];
        } else {
          trigger_error(__CLASS__ . "::Getter - Attribute " . $name . " not defined!", E_USER_ERROR);
        }
        break;
    }
  }

  public function add($text, $opt, $name, $forceValue = FALSE)
  {
    if (in_array($name, $this->_optStackKeys)) {
      throw new Exception(__METHOD__ . " - Option '" . $name . "' already set.");
    }

    if (in_array($opt, $this->_optStackKeys)) {
      throw new Exception(__METHOD__ . " - Option '" . $opt . "' already set.");
    }

    $item = array();
    $item['name']       = $name;
    $item['opt']        = $opt;
    $item['text']       = $text;
    $item['forceValue'] = $forceValue;

    $this->_optStack[$name] = $item;

    $this->_optStackKeys[] = $name;
    $this->_optStackKeys[] = $opt;
  }

  public function activate()
  {
    if (!isset($this->_optStack['help'])) {
      $this->add('Show this usage screen', 'h', 'help');
    }

    $optionString = "";
    $optionArray = array();

    foreach ($this->_optStack as $opt) {
      $option     = $opt['opt'];
      $longOption = $opt['name'];

      if (TRUE === $opt['forceValue']) {
        $option     .= ':';
        $longOption .= ':';
      }
      $optionString .= $option;
      $optionArray[] = $longOption;
    }

    // Get the command line options
    $options = getopt($optionString, $optionArray);

    foreach ($this->_optStack as $opt) {
      if (isset($options[$opt['opt']]) || isset($options[$opt['name']])) {
        $this->_theStack[$opt['name']] = TRUE;
        if (TRUE === $opt['forceValue']) {
          if (isset($options[$opt['name']])) {
            $value = $options[$opt['name']];
          } else if (isset($options[$opt['opt']])) {
            $value = $options[$opt['opt']];
          }

          $value = ltrim($value, '-');
          if (in_array($value, $this->_optStackKeys)) {
            $this->usage();
          } else {
            $this->_theStack[$opt['name']] = $value;
          }
        }
      } else {
        $this->_theStack[$opt['name']] = FALSE;
      }
    }

    if (isset($this->_theStack['help']) && (1 == $this->_theStack['help'])) {
      $this->usage();
    }
  }


  public function usage()
  {
    global $argv;

    $usageString = '';
    $usageString .= "Usage: " . $argv[0] . " [ options ]\n";
    $usageString .= "Options:\n";

    // first determine the padding for option display
    $pad = 0;
    foreach ($this->_optStack as $key => $opt) {
      $param = '';
      if ($opt['forceValue']) {
         $param .= ' <' . $opt['name'] . '>';
      }
      $this->_optStack[$key]['param'] = $opt['name'] . $param;
      $pad = max($pad, (strlen($this->_optStack[$key]['param']) + 2));
    }

    foreach ($this->_optStack as $key => $opt) {
      $usageString .= "\t" . "-" . $opt['opt'] . "  --" . str_pad($opt['param'], $pad) . $opt['text'] . "\n";
      unset($this->_optStack[$key]['param']);
    }

    echo $usageString . "\n";
    exit;
  }
}
