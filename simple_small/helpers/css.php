<?php defined('SYSPATH') OR die('No direct access allowed.');

//simple helper for including css from the controller
class css_Core {
	static protected $styles = array();

	static public function add($file)
	{
		self::$styles[] = $file;
	}

	static public function render($print = FALSE)
	{
		$output = '';
		foreach (self::$styles as $style)
			$output .= html::stylesheet($style,'screen,print');

		if ($print)
			echo $output;

		return $output;
	}
} // End css_Core