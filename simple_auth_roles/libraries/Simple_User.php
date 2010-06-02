<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
* Simple_User - simple class to strore user data in session
*
* @package		Simple_Auth
* @author			thejw23
* @copyright		(c) 2009 thejw23
* @license		http://www.opensource.org/licenses/isc-license.txt
* @version		1.0
* @last change		
*/
class Simple_User_Core {

	// user data
	protected $data = Array();
	
	// user roles
	public $roles = Array();
	
	// user details
	public $details = Array();
	
	// is loaded
	public $loaded = FALSE;
	
	// array, 'form field name' => 'database field name'
	public $aliases = Array(); 
	
	/**
	*  return user data
	*
	* @return array
	*/ 
	public function as_array()
	{
		return $this->data;
	}

	/**
	*  Magic get from $data	
	*
	* @param string $key key to be retrived
	* @return mixed
	*/
	public function __get($key)
	{    
		$key = $this->check_alias($key);

		if (array_key_exists($key, $this->data))
		{
			return $this->data[$key];
		}
		return NULL;
	}
	
	/**
	*  set user data	
	*
	* @param array $data array with user data
	* @return void
	*/
	public function set_user($data = NULL)
	{
		if (!empty($data) AND is_array($data))
		{
			$this->data = $data;
			$this->loaded = TRUE;
		}
	}
	
	public function set_roles($data = NULL)
	{
		if (!empty($data) AND is_array($data))
		{
			$this->roles = $data; 
		} 
	}
	
	/**
	*  clear user data	
	*
	* @return void
	*/
	public function unset_user()
	{
			$this->data = array();
			$this->roles = NULL;
			$this->loaded = FALSE;
	}
	
	/**
	*  Checks if given key is an alias and if so then points to aliased field name	
	*
	* @param string $key key to be checked
	* @return boolean
	*/
	public function check_alias($key)
	{
		return array_key_exists($key, $this->aliases) === TRUE ? $this->aliases[$key] : $key;
	}
	
	public function check_role($role = NULL)
	{
		if (! $this->loaded)
			return FALSE;
						
			if (intval($role) !== 0)
				return array_key_exists($role, $this->roles);
				
			if (is_string($role))
				return in_array($role, $this->roles);
						
		return FALSE;
	}

}
