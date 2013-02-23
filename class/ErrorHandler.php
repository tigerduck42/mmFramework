<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ErrorHandler
 *
 * @author martin
 */
class ErrorHandler {
	
	const WEB = 1;
	const MAIL = 2;
	
	private $_scope = NULL;
	private $_no = NULL;
	private $_string = NULL;
	private $_file = NULL;
	private $_line = NULL;
	private $_context = NULL;
	private $_mailTo = NULL;
	
	public function __construct($scope=self::WEB) {
		$this->_scope = $scope;
		$this->_mailTo = ERROR_MAIL_TO;
	}
	
	
	public function __set($name, $value) {
		switch($name) {
			case 'no':
				$this->_no = $value;
				break;
			case 'string':
				$this->_string = $value;
				break;
			case 'file':
				$this->_file = $value;
				break;
			case 'line':
				$this->_line = $value;
				break;
			case 'context':
				$this->_context = $value;
				break;
			case 'mailTo':
				$this->_mailTo = $value;
				break;
			default:
				throw new exception(__CLASS__ . "::Setter: Attribute " . $name . " not defined!");
				break;
		}
	}
	
	
	public function output() {
		if($this->_scope & self::WEB) {
			echo $this->_outputWeb();
		}
		
		if($this->_scope & self::MAIL) {
			$this->_outputMail();
		}
		
	}
	
	private function _outputWeb() {
		echo $this->_buildMessageBox();
	}
  
	private function _buildMessageBox($addContext = FALSE) {
		$html = '';
		$html .= '<div style="float: left; clear: both; overflow: hidden; border: 1px solid; padding: 10px; font-family: Verdana,Arial,sans-serif; font-size: 12px;">';
		$html .= '<b>Error:</b> ' . $this->_string . '<br />';
		$html .= '<b>File:</b> ' . $this->_file  . ' (' . $this->_line  . ')<br/>';
		
		$stackCore = array_reverse(debug_backtrace());
		
		$stack = array();
		foreach ($stackCore as $stackData) {
			if(isset($stackData['args'])) {
				unset($stackData['args']);
			}
			if(isset($stackData['object'])) {
				unset($stackData['object']);
			}
			
			$stack[] = $stackData;
		}
			
		$btHtml = "";
		foreach($stack as $depth => $rec) {
			if($rec['function'] == 'customError') {
				break;
			}

			$btHtml .= $depth . ': ';
			if(isset($rec['class'])) {
				$btHtml .= $rec['class'] . '::';
			}
			$btHtml .= $rec['function'] . '(';
			if(isset($rec['args'])) {
				$btHtml .= implode(',', $rec['args']);
			}
			$btHtml .= ')';

			if(isset($rec['file']) && isset($rec['line'])) {
				$btHtml .= ', at ' . $rec['file'] . ' line ' . $rec['line'] . '<br/>';
			}
		}

		if(strlen($btHtml)) {	
			$html .= '<br/><b>Backtrace:</b><br/>';
			$html .= $btHtml;
		}
		
		if($addContext && count($stack)) {
			$html .= '<br/><b>Stack</b>';
			$html .= '<pre>' . print_r($stack,1) . '</pre>';
		}
		
		if($addContext && count($this->_context)) {
			$html .= '<br/><b>Context</b>';
			$html .= '<pre>' . print_r($this->_context,1) . '</pre>';
		}
		
		$html .= '</div>';
		
		return $html;
	}
  
  
	private function _outputMail() {
		$mail = new MyMailer();
		$mail->From = $this->_mailTo;
		$mail->FromName = "WEBError";
		$mail->AddAddress($this->_mailTo, "WebAdmin");
		$mail->Subject = "Error on " . $_SERVER['SERVER_NAME'];
		$mail->IsHTML();
		$mail->Body = $this->_buildMessageBox(TRUE);

		if(!$mail->Send()) {
			echo $mail->ErrorInfo . "<br/>";
		}
	}
	
	public function send($msg) {
		$mail = new MyMailer();
		$mail->From = $this->_mailTo;
		$mail->FromName = "WEBError";
		$mail->AddAddress($this->_mailTo, "WebAdmin");
		$mail->Subject = "Error on " . $_SERVER['SERVER_NAME'];
		$mail->IsHTML();
		$mail->Body = "<p>" . $msg . "</p>";

		if(!$mail->Send()) {
			echo $mail->ErrorInfo . "<br/>";
		}
	}
	

}

?>
