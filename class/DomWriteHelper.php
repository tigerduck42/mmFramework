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

class DomWriteHelper
{

  private $_xpathContext = NULL;
  private $_hasNamespaceHack = FALSE;
  private $_before = NULL;
  private $_docStack = array();
  private $_nsStack = array();
  private $_doc = NULL;
  private $_encoding = NULL;
  private $_isHtml = FALSE;


  public function __construct($encoding = 'utf-8')
  {
    $this->_doc = new \DOMDocument('1.0', $encoding);
    $this->_encoding = $encoding;
  }

  public function __get($name)
  {
    switch($name) {
      default:
        trigger_error(__CLASS__ . "::Getter: Attribute " . $name . " not defined", E_USER_ERROR);
        break;
    }
  }

  public function __set($name, $value)
  {
    switch($name) {
      default:
        trigger_error(__CLASS__ . "::Getter: Attribute " . $name . " not defined", E_USER_ERROR);
        break;
    }
  }

  public function free()
  {
    if (isset($this->_doc)) {
      while ($this->_doc->hasChildNodes()) {
        $this->_doc->removeChild($this->_doc->firstChild);
      }
      $this->_doc = NULL;
    }
  }

  private function _normalize($force = FALSE)
  {
    if ($force or $this->_hasNamespaceHack) {
      $this->_doc->loadXML($this->_doc->saveXML());
    }
    $this->_hasNamespaceHack = FALSE;
  }

  // Convenience methods ////////////////////////////////////////////

  // Seek the insertion point to after the last child of $node.
  public function seek($node)
  {
    if (XML_ELEMENT_NODE != $node->nodeType) {
      return; // could bomb here...
    }
    $this->_docStack = array();
    $this->_nsStack = array();
    do {
        array_unshift($this->_docStack, $node);
      $node = $node->parentNode;
    } while ($node);
  }

  // Seek the insertion point to the root of the document
  public function seekRoot($clearChildren = TRUE)
  {
    $root = NULL;
    if ($this->_doc->hasChildNodes()) {
      $root = $this->_doc->firstChild;
    }

    if (!is_NULL($root)) {
      if ($clearChildren) {
        // Close dom except the root element
        $openNodesCount = count($this->_docStack);
        if ($openNodesCount > 1) {
          for ($i=1; $i<$openNodesCount; $i++) {
            $this->closeNode();
          }
        }

        if ($openNodesCount == 0) {
          bomb("Dom closed: Can't find children");
        } else {
          $top = $this->top();
          $childNodes = $top->childNodes;
        }

        foreach ($childNodes as $node) {
          $this->remove($node);
        }
      } else {
        $topChild = $root->firstChild;
        $this->_before = $topChild;
      }
    }
  }

  // Seek to the first node in the nodeset returned by querying $path.
  public function seekQ($path, $relative = NULL)
  {
    $nset = $this->query($path, $relative);
    if ($nset && $node = reset($nset)) {
      $this->seek($node);
      return $node;
    }
  }

  // Make the next element insertion _before the first node in the
  // nodeset returned by querying $path.  The path is relative to
  // the current insertion node.
  private function _before($path)
  {
    $nset = $this->query($path, $this->top());
    if ($nset && $node = reset($nset)) {
      $this->_before = $node;
      return $node;
    } else {
      unset($this->_before);
    }
  }

  /**
   * Return the serialized XML document.
   *
   * @param $nopi omit the XML declaration
   * @param $ws boolean whether to pretty-indent the ouput
   */
  public function serialize($nopi = FALSE, $ws = TRUE)
  {
    if ($this->_isHtml) {
      $data = $this->_doc->saveHTML();
    } else {
      $this->_doc->formatOutput = $ws;
      if ($nopi) {
        $data = $this->_doc->saveXML($this->_doc->documentElement);
      } else {
        $data = $this->_doc->saveXML();
      }
    }
    return $data;
  }

  private function _top()
  {
    if (empty($this->_docStack)) {
      $top =& $this->_doc;
    } else {
      $top =& end($this->_docStack);
    }
    return $top;
  }

  // Clone $node (possibly from another document) and insert under
  // the current element.  Returns the adopted node but does NOT
  // seek to it.
  public function adopt($node)
  {
    $top = $this->_top();
    $clone = $this->_doc->importNode($node, TRUE);
    if ($this->_before) {
      $top->insert_before($clone, $this->_before);
      $this->_before = NULL;
    } else {
      $top->appendChild($clone);
    }
    return $clone;
  }

  // remove $node from the document
  public function remove($node)
  {
    $parent = $node->parentNode;
    $parent->removeChild($node);
  }

  public function openElement($tag)
  {
    if (!$this->_doc) {
      bomb("Attempted write to NULL document");
    }
    $node =& $this->_doc->createElement($tag);
    $top = $this->_top();
    array_push($this->_docStack, $node);
    array_push($this->_nsStack, end($this->_nsStack));
    if ($this->_before) {
      $parent = $this->_before->parentNode;
      $parent->insert_before($node, $this->_before);
      $this->_before = NULL;
    } else {
      $top->appendChild($node);
    }
    return $node;
  }

  // Create an element with namespace $nsuri using the prefix
  // $prefix.  To use the default namespace, omit $prefix.
  public function openElementNs($tag, $nsuri, $prefix = NULL)
  {
    $ns = end($this->_nsStack);
    if (is_NULL($nsuri) && $ns[$prefix]) {
      $nsuri = $ns[$prefix];
    }
    if ($prefix) {
      $node = $this->_doc->createElementNs($nsuri, "$prefix:$tag");
    } else {
      $node = $this->_doc->createElementNs($nsuri, $tag);
    }
    if (empty($this->_docStack)) {
      $top =& $this->_doc;
    } else {
      $top =& end($this->_docStack);
    }
    array_push($this->_docStack, $node);
    $ns[$prefix] = $nsuri;
    array_push($this->_nsStack, $ns);
    $top->appendChild($node);
    return $node;
  }

  // legacy namespace-via-attribute needed by the PHP 4 implementation.
  // Note this is still valid, see:
  // http://www.w3.org/TR/2000/REC-DOM-Level-2-Core-20001113/core.html#Namespaces-Considerations
  public function xmlns($uri, $prefix = NULL)
  {
    $this->_hasNamespaceHack = TRUE;
    $top = end($this->_docStack);
    $nstop = end($this->_nsStack);
    if (!$nstop) {
      $nstop = array();
    }
    // force onto the default namespace
    if (empty($prefix)) {
      $top->setAttribute('xmlns', $uri);
    } else {
      $top->setAttribute('xmlns:'.$prefix, $uri);
    }

    $nstop[$prefix?$prefix:''] = $uri;
    $this->_nsStack[count($this->_nsStack)-1] = $nstop;
  }

  public function singleton($tag, $text = '')
  {
    $container = $this->openElement($tag);
    if ('' !== $text) {
      $this->textNode($text);
    }
    $this->closeNode();
    return $container;
  }

  public function singletonNs($tag, $nsuri, $prefix = NULL, $text = '')
  {
    $this->openElementNs($tag, $nsuri, $prefix);
    if ('' !== $text) {
      $this->textNode($text);
    }
    $this->closeNode();
  }

  // maybe use this its a bit more versatile but requires mbstring
  public function encodeToUtf8($string)
  {
    /*
    $string = mb_convert__encoding($string, "UTF-8", mb_detect__encoding($string, "UTF-8, ISO-8859-1", TRUE));
    if (strtolower($this->_encoding) != 'utf-8') { // hack to stop libxml sending unicode in entities, try and convert it to its closest equivelent
      $string = iconv("UTF-8","{$this->_encoding}//TRANSLIT//IGNORE", $string); // switch to our target _encoding translating then taking away as many unencodable chars as possible
      $string = mb_convert__encoding($string, "UTF-8", $this->_encoding); // switch back to utf8 _before sending it away
    }
    */
    return $string;
  }

  public function textNode($content)
  {
    //$sanitise = utf8_encode(htmlspecialchars($content)); // changed by andrew 20080903
    $sanitise = $this->encodeToUtf8($content);
    $node =& $this->_doc->createTextNode((string)$sanitise);
    $top =& end($this->_docStack);
    if ($top) {
      $top->appendChild($node);
    }
  }

  public function cdata($content)
  {
    $node =& $this->_doc->createCDATASection($content);
    $top =& end($this->_docStack);
    if ($top) {
      $top->appendChild($node);
    }
  }

  public function commentNode($content)
  {
    $sanitise = $this->encodeToUtf8($content);
    $node =& $this->_doc->createComment((string)$sanitise);
    $top =& end($this->_docStack);
    if ($top) {
      $top->appendChild($node);
    } else {
      $this->_doc->appendChild($node);
    }
  }

  public function attribute($name, $value)
  {
    $top = end($this->_docStack);
    $sanitise = $this->encodeToUtf8($value);
    return $top->setAttribute($name, $sanitise);
  }

  public function removeAttribute($name)
  {
    $top =& end($this->_docStack);
    return $top->removeAttribute($name);
  }

  public function closeNode()
  {
    $node = array_pop($this->_docStack);
    array_pop($this->_nsStack);
    if (empty($this->_docStack)) {
      return FALSE;
    } else {
      $top =& end($this->_docStack);
      return TRUE;
    }
  }

  public function closeAll()
  {
    while ($this->closeNode()) {
      //loop
    }
  }

  /**
   * Create an XPath context for use with subsequent calls to
   * query().
   *
   * @param $namespaces prefix => namespace-uri array
   */
  public function setupQueries($namespaces = array())
  {
    $this->_normalize();
    $this->_xpathContext = new \DOMXPath($this->_doc);
    foreach ($namespaces as $prefix => $uri) {
      if (is_NULL($prefix)) {
        $prefix = 'default';
      }
      $this->_xpathContext->registerNamespace($prefix, $uri);
    }
  }

  /**
   * Execute an XPath location expression and return the result.
   * If provided, the query is relative to the $relative node.
   *
   * Note this will generally return an array even if only one node
   * matched.  To get strings, try qSingle().
   *
   * This is a shorter-form of runQuery that doesn't expose the XPath
   * object, just the result.  It also doesn't need to create a new
   * context every time.
   *
   * @param $query XPath location path
   * @param $relative DOMNode
   * @return nodeset, value or an empty array
   */
  public function query($query, $relative = NULL)
  {
    if (is_NULL($this->_xpathContext)) {
      $this->setupQueries();
    }
    if (is_NULL($relative)) {
      $xp = $this->_xpathContext->query($query);
    } else {
      $xp = $this->_xpathContext->query($query, $relative);
    }
    $rv = array();
    if (!empty($xp)) {
      foreach ($xp as $node) {
        $rv[] = $node;
      }
    }
    return $rv;
  }

  public function query1st($query, $relative = NULL)
  {
    if (is_NULL($this->_xpathContext)) {
      $this->setupQueries();
    }
    if (is_NULL($relative)) {
      $xp = $this->_xpathContext->query($query);
    } else {
      $xp = $this->_xpathContext->query($query, $relative);
    }

    foreach ($xp as $node) {
      return $node;
    }

  }

  /**
   * Like query(), but only return only the contents of the first
   * match, as a string.
   */
  public function qSingle($query, $relative = NULL)
  {
    $result = $this->query($query, $relative);

    if (!is_NULL($result)) {
      foreach ($result as $node) {
        return $node->nodeValue;
      }
    }

    return NULL;
  }

  /**
   * Parse $xml.  Return a string describing the error, or NULL if
   * there were no errors.
   *
   * Note the underlying document will be freed and this object
   * must not be used there were errors.
   */
  public function createXmlParserAndParse($xml)
  {

    // if there's something already existing, clear it
    //$this->free();

    if (!strlen($xml)) {
      return "Parse error: empty xml string";
    }

    $errors = array();

    $this->_doc = new \DOMDocument('1.0', $this->_encoding);
    $this->_doc->preserveWhiteSpace = FALSE;
    libxml_use_internal_errors(TRUE);
    if (defined(LIBXML_COMPACT)) {
      $this->_doc->loadXML($xml, LIBXML_COMPACT);
    } else {
      $this->_doc->loadXML($xml);
    }
    $errors = libxml_get_errors();
    libxml_clear_errors();

    if ($errors) {
      $errorText = 'Parse failure: ';
      if (FALSE !== strpos($xml, '<!DOCTYPE HTML PUBLIC') || FALSE !== strpos($xml, '<html')) {
        $errorText = " looks like HTML";
      } else if (is_array($errors)) {
        foreach ($errors as $error) {
          $errorText .= $error->line .':'. $error->column . ' ' . $error->message . "\n";
        }
      } else {
        $errorText = 'Parse failure';
      }
      return $errorText;
    }
    if (empty($this->_doc)) {
      return "Parse error";
    }
    return NULL;
  }

  public function schemaValidate($filename)
  {
    return $this->_doc->schemaValidate($filename);
  }

  /**
   * Parse $html.  Return a string describing the error, or NULL if
   * there were no errors.
   *
   * Note the underlying document will be freed and this object
   * must not be used there were errors.
   */
  public function createHtmlParserAndParse($html)
  {

    // if there's something already existing, clear it
    $this->free();

    if (!strlen($html)) {
      return "Parse error: empty html string";
    }

    $this->_isHtml = TRUE;

    $errors = array();
    $this->_doc = new \DOMDocument('1.0', $this->_encoding);
    $this->_doc->preserveWhiteSpace = FALSE;
    libxml_use_internal_errors(TRUE);
    $this->_doc->loadHTML($html);
    $errors = libxml_get_errors();
    libxml_clear_errors();

    if ($errors) {
      $errorText = 'Parse failure: ';
      if (is_array($errors)) {
        foreach ($errors as $error) {
          $errorText .= $error->line .':'. $error->column . ' ' . $error->message . "\n";
        }
      } else {
        $errorText = 'Parse failure';
      }
      return $errorText;
    }

    if (empty($this->_doc)) {
      return "Parse error";
    }

    return NULL;
  }
}
// End of class

function DomNodeContent($node)
{
  return $node->nodeValue;
}

function DomElementTagname($element)
{
  return $element->tagName;
}
