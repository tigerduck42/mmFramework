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

/**
 * {@link MmOutputRenderer} for Smarty.
 * <p>Smarty is a template engine. See {@link http://www.smarty.net/}</p>
 * @package MmFramework
 * @uses MmOutputRenderer
 */
class OutputRendererSmarty extends OutputRenderer
{
  private $_template      = NULL;
  private $_templateDirs  = NULL;
  private $_templateExtra = NULL;
  private $_smarty        = NULL;

  //
  // --------------------------------------------------------------
  // METHODS
  //

  /**
   * Create a new MuOutputRendererSmarty
   * @param string|array $templateDir A single template dir, or a set of directories to search in.
   * @param string $templateCompileDir
   * @param bool $forceRecompile
   */
  public function __construct($templateDir = NULL, $templateCompileDir = NULL, $forceRecompile = NULL)
  {
    if (TRUE == is_null($templateCompileDir)) {
      $templateCompileDir = DIR_BASE . '/template_c';
    }

    $this->_smarty = new \Smarty();
    $this->_smarty->compile_dir  = $templateCompileDir;

    if (is_null($forceRecompile)) {
      $config = Config::getInstance();
      $this->_smarty->force_compile = $config->smartyForceRecompile;
    } else {
      $this->_smarty->force_compile = $forceRecompile;
    }
    // Set the template directory(ies)
    $this->setTemplateDir($templateDir);
  }

  /**
   * Magic getter
   * @param  string $name Name of property
   * @return void
   */
  public function __get($name)
  {
    switch($name) {
      default:
        return parent::__get($name);
        break;
    }
  }

  /**
   * Magic setter
   * @param  string $name Name of property
   * @param  mixed $value Name of property
   * @return mixed
   */
  public function __set($name, $value)
  {
    switch($name) {
      default:
        parent::__set($name, $value);
        break;
    }
  }

  /**
   * Set the template directory/directories.
   * @param string|array $templateDir A single template dir, or a set of directories to search in.
   */
  public function setTemplateDir($templateDir)
  {
    if (TRUE == is_null($templateDir)) {
      $this->_templateDirs = array(DIR_BASE . '/template');
    } else if (is_string($templateDir)) {
      $this->_templateDirs[] = $templateDir;
    } else if (is_array($templateDir)) {
      $this->_templateDirs = $templateDir;
    }

    // Set the template directory to be the first entry.
    $this->_smarty->template_dir = $this->_templateDirs;
  }

  /**
   * Checks to see if a smarty varable has already been defined
   *
   * @param string $templateVariable
   * @return bool
   */
  public function varIsSet($templateVariable)
  {
    assert(is_string($templateVariable));
    assert(strlen($templateVariable) > 0);

    $var = $this->_smarty->get_template_vars($templateVariable);

    return(FALSE == is_null($var));
  }

  /**
   * Smarty assign a variable.
   *
   * @param string $templateVariable
   * @param mixed $value
   */
  public function assign($templateVariable, $value)
  {
    assert(is_string($templateVariable));
    assert(strlen($templateVariable) > 0);

    $this->_smarty->assign($templateVariable, $value);
  }

  /**
   * Smarty add a assign to a variable.
   *
   * @param string $templateVariable
   * @param mixed $value
   */
  public function assignAdd($templateVariable, $value)
  {
    assert(is_string($templateVariable));
    assert(strlen($templateVariable) > 0);

    if (isset($this->_smarty->tpl_vars[$templateVariable])) {
      $smartyVar =& $this->_smarty->tpl_vars[$templateVariable];
      $smartyVar->value .= $value;
    } else {
      $this->_smarty->assign($templateVariable, trim($value));
    }
  }

  /**
   * Set the main layout template.
   *
   * @param string $template
   * @param array $templateArray
   */
  public function setTemplate($template, array $templateArray = array())
  {
    assert(is_string($template));

    $this->_templateExtra = $templateArray;
    $this->_template = $template;
  }

  /**
   * Register a smarty function.
   *
   * @param string $name The name of the function
   * @param string/array $function The function specification.
   */
  public function registerFunction($name, $function)
  {
    $this->_smarty->register_function($name, $function);
  }

  /**
   * Register a smarty resource.
   *
   * @param string $name The name of the resource
   * @param array $resourceFuncs An array of functions that implement the resource
   */
  public function registerClass($name, $className)
  {
    assert(is_string($name) && (strlen($name) > 0));
    assert(is_string($className) && (strlen($className) > 0));

    $this->_smarty->registerClass($name, $className);
  }

  /**
   * Register a smarty resource.
   *
   * @param string $name The name of the resource
   * @param array $resourceFuncs An array of functions that implement the resource
   */
  public function registerResource($name, $resourceFuncs)
  {
    $this->_smarty->register_resource($name, $resourceFuncs);
  }

  /**
   * Register a smarty template directory.
   *
   * @param string $path Path of the plugin dorectory
   */
  public function registerPluginsDir($path)
  {
    assert(is_string($path) && file_exists($path));
    $currentDirs = $this->_smarty->getPluginsDir();

    if (!in_array($path, $currentDirs)) {
      $currentDirs[] = $path;
      $this->_smarty->setPluginsDir($currentDirs);
    }
  }

  /**
   * Output the smarty template.
   * @return string
   */
  public function output($file = NULL)
  {
    if (FALSE == is_null($this->_template)) {
      if (FALSE == is_null($this->_templateExtra)) {
        // Store in the javascript/css tags.
        $this->assign("__javascript", $this->_javascript);
        $this->assign("__javascriptCode", $this->_javascriptCode);

        // Legacy code
        $theUrls = array();
        foreach ($this->_links as $linkObj) {
          $theUrls[] = $linkObj->url;
        }
        $this->assign("__links", $theUrls);

        $this->assign("__linksObj", $this->_links);

        foreach ($this->_templateExtra as $variable => $value) {
          $this->assign($variable, $this->_smarty->fetch($value));
        }
      }

      $this->_output = $this->_smarty->fetch($this->_template);
    }


    if (FALSE == is_null($file)) {
      file_put_contents($file, $this->_output);
    }

    header('Content-type: ' . $this->contentType());
    return $this->_output;
  }

  /**
   * Output the smarty template.
   * @return string
   */
  public function outputText($file = NULL)
  {
    if (FALSE == is_null($this->_template)) {
      if (FALSE == is_null($this->_templateExtra)) {
        foreach ($this->_templateExtra as $variable => $value) {
          $this->assign($variable, $this->_smarty->fetch($value));
        }
      }

      $this->_output = $this->_smarty->fetch($this->_template);
    }

    if (FALSE == is_null($file)) {
      file_put_contents($file, $this->_output);
    }

    return $this->_output;
  }

  /**
   * Provide a compatible API to Smarty
   *
   * @return string
   */
  public function fetch()
  {
    return $this->output();
  }

  /**
   * Initialise the Renderer.
   */
  public function init()
  {
    // Nothing to initialise.
  }
}
