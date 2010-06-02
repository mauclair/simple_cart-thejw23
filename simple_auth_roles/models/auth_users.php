<?php defined('SYSPATH') or die('No direct script access.');
/**
* User Model
*
* @package		Simple_Auth
* @author			thejw23
* @copyright		(c) 2009 thejw23
* @license		http://www.opensource.org/licenses/isc-license.txt
* @version		1.0
* based on KohanaPHP Auth and Auto_Modeler
*/
class Auth_Users_Model extends Auth_Modeler {

	protected $table_name = 'auth_users';
		
	protected $data = array('id' => '',
						'username' => '',
						'password' => '',
						'email' => '',
						'logins' => '',
						'admin' => '',
						'active' => '',
						'active_to'=>'',
						'moderator' => '',
						'ip_address'=>'',
						'last_ip_address'=>'',
						'time_stamp'=>'',
						'last_time_stamp' => '',
						'time_stamp_created'=>''); 

	public $timestamp = array ();
	
	public $roles = array();

	/**
	* Constructor
	*
	* @param mixed $id unique user to be loaded	
	* @return void
	*/
	public function __construct($id = NULL)
	{
		parent::__construct();

		// if user id 
		if ($id != NULL AND (ctype_digit($id) OR is_int($id)))
		{
			// try and get a row with this ID
			$this->load($id);
		}
		// if username 
		elseif ($id != NULL AND is_string($id))
		{
			// try and get a row with this username/email
			$this->load($id, Kohana::config('simple_auth.unique'));
		}
		// if username and password
		elseif ($id != NULL AND is_array($id))
		{
			$data = array(Kohana::config('simple_auth.unique') => $id['username'], Kohana::config('simple_auth.password') => Simple_Auth::instance()->hash($id['password']));
			$this->load($data);
		}
	}
		
	public function load_roles($user_id = NULL)
	{
		
		if ($this->loaded() AND $user_id === NULL)
			$user_id = $this->id;
		
		if (intval($user_id) !== 0 )
		{
			$roles_model = new Auth_Roles_Model;
			$user_roles_model = new Auth_User_Roles_Model;
			
			$results = $this->db->from($roles_model->get_table_name().' AS ar')
						->select('ar.id','ar.role')
						->join($user_roles_model->get_table_name().' AS aur', 'ar.id', 'aur.role_id', 'LEFT')
						->where('aur.user_id',$user_id)
						->get()
						->result_array(TRUE);
						
			foreach ($results as $key=>$value)
				$this->roles[$value->id] = $value->role;
		}
	}

	/**
	 * Check if username exists in database.
	 *
	 * @param string $name username to check
	 * @param string $second second username to check 	 
	 * @return boolean
	 */
	public function user_exists($name, $second='')
	{
		if (!empty($second))
		{
			return (bool) $this->db->where(array(Kohana::config('simple_auth.unique')=>$name,Kohana::config('simple_auth.unique_second')=>$second))->count_records($this->table_name);
		}
		else
		{
			return (bool) $this->db->where(array(Kohana::config('simple_auth.unique')=>$name))->count_records($this->table_name);
		}
	}
	
} // End User_Model