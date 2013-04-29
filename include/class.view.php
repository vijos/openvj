<?php

//
// This file contains the View class which extends Blitz
//

class View extends Blitz
{
	private $m_params = array();
	
	function assign($name, $value)
	{
		$this->m_params[$name] = $value;
	}
	
	function display()
	{
		parent::display($this->m_params);
	}
	
	function execution_time()
	{
		global $_START_TIME;
		return round((microtime(TRUE) - $_START_TIME) * 1000, 5);
	}
}
