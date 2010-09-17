<?php 

class Session extends Session_Core {

	/**
	* show stored flash messages stored in array
	*
	* @return  string
	*/
	public static function flash() 
	{
		$flash = Session::get('flash');
		if (!empty($flash)) 
		{
			$out = '';
			foreach (array_reverse($flash) as $message)
			{
				$out .= '<div class="message-box '.$message['type'].'">'.$message['data'].'</div>';
			}
			Session::delete('flash');
			return $out;      
		}
	}

	/**
	* store flash mesasage to display 
	*
	* @param   string   css class name
	* @param   string   text to display
	* @return  nothing.
	*/
	public static function setflash($type = 'info', $value = '', $redirect = NULL, $method = '302')
	{
		if (empty($value))
			return FALSE;
		
		$flash[] = array('type' => $type,'data' => $value);
		$old = Session::get('flash');
		if (!empty($old))
		{
			foreach ($old as $message) 
			{ 
				$flash[] = $message;
			}
		}
		
		Session::set_flash('flash', $flash);
		
		if (!empty($redirect))
		{
			url::redirect($redirect, $method);
		}
	}
}