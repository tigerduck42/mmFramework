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

abstract class OutputRenderer
{

	/**
	 * Whether we should attempt to detect the content type of output automatically.
	 *
	 * @var bool $_detectContentType
	 */
	private $_detectContentType = FALSE;

	/**
	 * The content type of the output
	 *
	 * @var string $_contentType
	 */
	private $_contentType = 'text/html';

	/**
	 * The actual output for this page
	 *
	 * @var string $_output
	 */
	protected $_output = '';

	/**
	 * A list of javascript files to include.
	 * @var array $_javascript
	 */
	protected $_javascript = array();

	/**
	 * A list of javascript files to include.
	 * @var array $_javascriptCode
	 */
	protected $_javascriptCode = array();

	/**
	 * A list of MuHtmlRelationTag objects, containing items to become <link> tags.
	 * @var array $_links
	 */
	protected $_links = array();

	/**
	 * Initialise the Renderer
	 */
	abstract public function init();

	/**
	 * Collect the output from the renderer.
	 *
	 * FIXME: this method is not correctly named
	 */
	abstract public function output();

	/**
	 * Get the content type of the output returned
	 * @return string mime type of output
	 */
	public function contentType()
	{
		if( is_null($this->_contentType) )
		{
			$this->_contentType = $this->detectContentType($this->_output);
		}
		return $this->_contentType;
	}

	/**
	 * Set the content type of this output.
	 * @param string $type A valid Content-type e.g. text/html.
	 */
	public function setContentType( $type )
	{
		$this->_contentType = $type;
	}

	/**
	 * Detect the content type of the output
	 *
	 * Use the PHP fileinfo extension to automatically determine the content-type of the output. To use this
	 * the output must of been generated
	 *
	 * @param string $string
	 * @return string The mime type of the binary string
	 */
	public function detectContentType( $string )
	{
		if( extension_loaded('fileinfo') and class_exists('finfo') )
		{
			$magic = new finfo(FILEINFO_MIME);
			return $magic->buffer($string);
		}
		// TODO: throw exception or use fallback if fileinfo not available?
	}

	/**
	 * Add a Javascript file to this page.
	 * @param string $javascriptUrl The URL to the javascript file.
	 */
	public function addJavascript( $javascriptUrl )
	{
		assert(is_string($javascriptUrl));

		if( FALSE == in_array($javascriptUrl, $this->_javascript) )
		{
			$this->_javascript[] = $javascriptUrl;
		}
	}

	/**
	 * Add a Javascript code to this page.
	 * @param string $javascriptCode.
	 */
	public function addJavascriptCode( $javascriptCode )
	{
		assert(is_string($javascriptCode));
		$key = md5($javascriptCode);

		if( FALSE == in_array($key, array_keys($this->_javascriptCode)) )
		{
			$this->_javascriptCode[$key] = $javascriptCode;
		}
	}

	/**
	 * Add a CSS file to this page.
	 * @param string $cssUrl The URL to the CSS file.
	 * @param string $media The media type, defaults to screen.
	 * @return MuHtmlRelationTag The new tag, if you need to add extra attributes.
	 */
	public function addCss( $cssUrl, $media = "screen" )
	{
		assert(is_string($cssUrl));
		assert(is_string($media));

		foreach( $this->_links as $link )
		{
			if( $link == $cssUrl )
			{
				// This CSS url has already been added.
				return TRUE;
			}
		}
		$this->_links[] = $cssUrl;
	}
}

?>