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
class Auth_User_Details_Model extends Auth_Modeler {

	protected $table_name = 'auth_user_details';
		
	protected $data = array('id' => '',
						'user_id' => '',
						'name' => '',
						'lastname' => '',
						'last_time_stamp' => '',
						'time_stamp_created'=>''); 	
	
} // End User Details