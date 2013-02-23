<?php
/**
* @copyright 2010 Martin Mitterhauser
*
* @package MmFramework
*/

/**
 * @package MmFramework
 */
class MuOutputRendererOb extends MuOutputRenderer
{
	/**
	 * Initialise the Renderer
	 */
	public function init()
	{
		ob_start();
	}

	/**
	 * Get the output from this Renderer
	 * @return string
	 */
	public function &output($file=NULL)
	{
		$this->_output = ob_get_clean();
		
		if(FALSE == is_null($file))
		{
			file_put_contents($file, $this->_output);
		}
		
		return $this->_output;
	}

}

?>
