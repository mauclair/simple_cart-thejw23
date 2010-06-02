<?php defined('SYSPATH') or die('No direct script access.');
/**
* User Roles Model
*
* @package		Simple_Auth
* @author			thejw23
* @copyright		(c) 2009 thejw23
* @license		http://www.opensource.org/licenses/isc-license.txt
* @version		1.0
* based on KohanaPHP Auth and Auto_Modeler
*/
class Auth_User_Roles_Model extends Auth_Modeler {

	protected $table_name = 'auth_user_roles';

	protected $auto_trim = TRUE;

	protected $data = array('id' => '',
						'user_id' => '',
						'role_id' => '');
						
						
	public function delete_user_roles($user_id = NULL)
	{
		if (intval($user_id) !== 0)
		{
			return $this->db->delete($this->table_name,array('user_id'=>$user_id));
		}
	}
	
	/**
	* Insert data into database
	* return last insert id	
	*
	* @param array $data_to_save data to insert
	* @return integer
	*/
	public function insert($data_to_save = array()) 
	{
		if ( ! empty($data_to_save) AND is_array($data_to_save))
		{
			//insert data and get inserted id
			return $this->db->insert($this->table_name, $data_to_save)->insert_id();
		}
	}

} // End User Roles