<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
* Simple_Auth - user authorization library for KohanaPHP framework
*
* @package			Simple_Acl
* @author				thejw23
* @copyright			(c) 2009 thejw23
* @license			http://www.opensource.org/licenses/isc-license.txt
* @version			0.1

* based on Wouter ACL and Simple Auth/Modeler
*/
class Simple_Acl extends Acl {

	// Session instance
	protected $session;

	// Configuration
	protected $config;
	
	public $auth;
	private $user;
	protected $guest_role; 
	
	/**
	 * Creates a new class instance, loading the session and storing config.
	 *
	 * @param array $config configuration
	 * @return void
	 */
	public function __construct($config = array())
	{
		// Append default auth configuration
		$config += Kohana::config('simple_acl');
		
		// Load Session
		$this->session = Session::instance();

		// Save the config in the object
		$this->config = $config;

		// set debug message
		Kohana::log('debug', 'Simple_Acl Library loaded');
		
		$this->guest_role = $config['guest_role'];
		$this->auth = Simple_Auth::instance();
		$this->auth->login('test@email.com', 'test', true);
		
		if(!array_key_exists($this->guest_role,$config['roles']))
		{
			$this->add_role($this->guest_role);
		}

		// Add roles
		foreach($config['roles'] as $role => $parent)
		{
			$this->add_role($role,$parent);
		}

		// Add resources
		if(!empty($config['resources']))
		{
			foreach($config['resources'] as $resource => $parent)
			{
				$this->add_resource($resource,$parent);
			}
		}

		// Add rules
		foreach(array('allow','deny') as $method)
		{
			if(!empty($config['rules'][$method]))
			{
				foreach($config['rules'][$method] as $rule)
				{
					if( ($num = 4 - count($rule)) )
					{
						$rule += array_fill(count($rule),$num, NULL);
					}
					
					// create assert object
					if($rule[3] !== NULL)
						$rule[3] = isset($rule[3][1]) ? new $rule[3][0]($rule[3][1]) : new $rule[3][0];
					
					$this->$method($rule[0],$rule[1],$rule[2],$rule[3]);
				}
			}
		}
		
	}
	
	
	/**
	 * Create an instance of Simple_Auth.
	 *
	 * @param array $config configuration	 
	 * @return object
	 */
	public static function factory($config = array())
	{
		return new Simple_Acl($config);
	}

	/**
	 * Return a static instance of Simple_Auth.
	 *
	 * @param array $config configuration 
	 * @return object
	 */
	public static function instance($config = array())
	{
		static $instance;

		// Load the Acl instance
		empty($instance) and $instance = new Simple_Acl($config);

		return $instance;
	}
	
	
	public function allowed($resource = NULL, $privilige = NULL)
	{
		// retrieve user
		$role = ($user = Simple_Auth::instance()->get_user()) ? $user : $this->guest_role;
		return $this->is_allowed($role,$resource,$privilige);
	}
	
	// Alias of the logged_in method
	public function logged_in() 
	{
		return Simple_Auth::instance()->logged_in();
	}

	// Alias of the get_user method
	public function get_user()
	{
		return Simple_Auth::instance()->get_user();
	}
	

}
