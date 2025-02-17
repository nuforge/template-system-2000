<?php
////////////////////////////////////////////////////
// User - PHP msg Handler Class
//
// Class to handle msgs in php for 
// iMonetize.com
//
// Copyright (C) 2006  iMonetize
//
// License: LGPL, see LICENSE
////////////////////////////////////////////////////

/**
 * @package iMonetize - msg
 * @author nuForge
 * @copyright 2006 iMonetize.com
 */
class messageHandler
{

	private $my_type;
	private $my_message = array();

	function __construct($type = 'fieldset')
	{
		$this->my_type = $type;
	}

	public function add($add_status, $add_message = true)
	{
		$this->my_message[$add_status][] = $add_message;
	}

	public function num($a_status)
	{
		return count($this->my_message[$a_status]);
	}

	public function output($o_status)
	{
		if (!empty($this->my_message[$o_status])) {
			if ($this->my_type == 'fieldset') {
				$t_string = '<' . $this->my_type . ' id="message" class="' . $o_status . '" >';
				$t_string .= '<legend>' . $o_status . '</legend>';
				$t_string .= '<ul>';
				foreach ($this->my_message[$o_status] as $msg) {
					$t_string .= '<li>' . $msg . '</li>' . "\n";
				}
				$t_string .= '</ul>';
				$t_string .= '</' . $this->my_type . '>';
			} else {
				$t_string = '<' . $this->my_type . ' id="message" class="' . $o_status . '" >';
				$t_string .= '<ul>';
				foreach ($this->my_message[$o_status] as $msg) {
					$t_string .= '<li>' . $msg . '</li>' . "\n";
				}
				$t_string .= '</ul>';
				$t_string .= '</' . $this->my_type . '>';
			}
			return $t_string;
		} else {
			return false;
		}
	}

	public function status($st_status)
	{
		return (isset($this->my_message[$st_status]));
	}

	public function merge($msg) {}
}
