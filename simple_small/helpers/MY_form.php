<?php defined('SYSPATH') OR die('No direct access allowed.');

class form extends form_Core {

	/**
	 * show html span element with error message 
	 *
	 * @param   string   error message to display
	 * @param   string   css class	 
	 * @return  string
	 */
	public static function my_errors($error='',$class='errors')
	{
		return (!empty($error)) ? '<span class="'.$class.'">'.$error.'</span>' : null;
	}
	
	public static function make_input ($form = array(), $errors = array(),$name = '', $label = '')
	{
		$out = '<p class="label_input">'
		.self::label($name, $label.': ').self::input($name, $form[$name])
		.self::my_errors($errors[$name]).'</p>'.'<div class="clear"></div>';
		
		return $out;
	}
	
	public static function make_checkbox ($form = array(), $errors = array(), $name = '', $label = '', $value = 1)
	{
		$out = '<p class="label_input">'
		.self::label($name, $label.': ').self::checkbox($name, $value, !empty($form[$name]))
		.self::my_errors($errors[$name]).'</p>'.'<div class="clear"></div>';
		
		return $out;
	}
	
	public static function make_dropdown ($form = array(), $errors = array(), $name = '', $label = '', $values = array())
	{
		$out = '<p class="label_input">'
		.self::label($name, $label.': ').self::dropdown($name, $values, $form[$name])
		.self::my_errors($errors[$name]).'</p>'.'<div class="clear"></div>';
		
		return $out;
	}
	
	public static function make_textarea ($form = array(), $errors = array(), $name = '', $label = '', $styles, $break = '<br />')
	{
		$out = '<p class="label_input">'.$break
		.self::label($name, $label.': ').self::textarea($name, $form[$name],$styles)
		.self::my_errors($errors[$name]).'</p>'.'<div class="clear"></div>';
		
		return $out;
	}

}