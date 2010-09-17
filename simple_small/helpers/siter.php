<?php defined('SYSPATH') or die('No direct script access.');

class siter_Core {

	public static function is_even($int_str) 
	{
		return (int)!($int_str & 1);
	}
	
     public static function get_breadcrumbs($input_array = null)
     {
     	$i = 1;
          $out = '';
     	$count = count($input_array)-1;

          foreach ($input_array as $url => $title) {
     		if($i <= $count)
     		{
     			$out .= html::anchor($url,ucwords(inflector::humanize($title))).' / ';
     		}
     		else
     		{
     			$out .= strip_tags("<strong>".ucwords($title)."</strong>", "<strong>");
     		}
     		
     		$i++;     
          }
     	
          return $out;
     } 
     
     
	public static function pre_dump($data) 
	{ 
		echo "<pre>";
		var_dump($data);
		echo "</pre>"; 
	}

}
?>