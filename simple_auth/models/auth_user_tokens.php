<?php defined('SYSPATH') or die('No direct script access.');
/**
* User Token Model
*
* @package		Simple_Auth
* @author			thejw23
* @copyright		(c) 2009 thejw23
* @license		http://www.opensource.org/licenses/isc-license.txt
* @version		1.0
* based on KohanaPHP Auth and Auto_Modeler
*/
class Auth_User_Tokens_Model extends Auth_Modeler {

	protected $table_name = 'auth_user_tokens';

	protected $auto_trim = TRUE;

	protected $data = array('id' => '',
						'user_id' => '',
						'expires' => '',
						'created' => '',
						'user_agent' => '',
						'time_stamp' => '',
						'token' => '');

	// Current timestamp
	protected $now;

	/**
	* Constructor
	*
	* @param integer $id unique token to be loaded	
	* @return void
	*/
	public function __construct($id = FALSE)
	{
				// parent::__construct($id);
				parent::__construct();
		
				// Set the now, we use this a lot
				$this->now = date('Y-m-d H:i:s');
		
				if ($id != NULL AND is_string($id))
				{
					// try and get a row with this token
					$this->load($id, 'token');
					
					if ($this->loaded())
					{
						// if token expired delete all expired, delete cookie and clear loaded data
						if ($this->data['expires'] < $this->now) 
						{
							$this->delete_expired();
							$this->clear_data();
							cookie::delete(Kohana::config('simple_auth.cookie_key'));
						}
					} 
					else //invalid token, delete cookie 
					{
						cookie::delete(Kohana::config('simple_auth.cookie_key'));
					}
				}
	}

	/**
	* Overload saving to set the created time and to create a new token
	* when the object is saved.	
	*
	* @return void
	*/
	public function save()
	{
		if ($this->data[$this->primary_key] == 0)
		{
			// Set the created time, token, and hash of the user agent
			$this->data['created'] = $this->now;
			$this->data['user_agent'] = sha1(Kohana::$user_agent);
		}

		// Create a new token each time the token is saved
		$this->data['token'] = $this->create_token();

		return parent::save();
	}

	/**
	 * Deletes all expired tokens.
	 *
	 * @return integer
	 */
	public function delete_expired()
	{
		return $this->db->delete($this->table_name,array('expires <' => $this->now));
	}
	
	
	/**
	 * Deletes all expired tokens and the user old tokens for current user_agent.
	 *
	 * @param integer $id unique user id to delete tokens
	 * @return mixed
	 */
	public function delete_user_tokens($id = 0, $all = FALSE)
	{
		$this->delete_expired();
		
		if (intval($id) === 0) 
			return FALSE;
		
		if ($all)
			return $this->db->delete($this->table_name,array('user_id'=>$id));
		else
			return $this->db->delete($this->table_name,array('user_id'=>$id,'user_agent'=>sha1(Kohana::$user_agent)));
	} 

	/**
	 * Finds a new unique token, using a loop to make sure that the token does
	 * not already exist in the database. This could potentially become an
	 * infinite loop, but the chances of that happening are very unlikely.
	 *
	 * @return  string
	 */
	protected function create_token()
	{
		while (TRUE)
		{
			// Create a random token
			$token = text::random('alnum', 32);

			// Make sure the token does not already exist
			if (count($this->db->select('id')->from($this->table_name)->where('token', $token)->get($this->table)) === 0)
			{
				// A unique token has been found
				return $token;
			}
		}
	}

} // End User Tokens Model