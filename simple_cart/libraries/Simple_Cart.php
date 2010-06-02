<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
* Simple_Cart - simple cart module for KohanaPHP framework. uses Simple_Modeler for database support.
*
* @package			Simple_Cart
* @author				thejw23
* @copyright			(c) 2009 thejw23
* @license			http://www.opensource.org/licenses/isc-license.txt
* @version			1.1
* @last change			added database support
*/

class Simple_Cart_Core {
	
	// Configuration
	protected $config;
	
	protected $user;
	protected $use_db = TRUE;
	
	//Creates a new class instance, loading the session and storing config
	public function __construct()
	{
		$this->config = Kohana::config_load('simple_cart');
		if (Simple_Auth::instance()->logged_in(NULL))
		{
			$this->user = Simple_Auth::instance()->get_user();
		}
		
		if ($this->use_db AND empty($_SESSION[$this->config['cart_key']]) AND is_object($this->user))
		{
			$this->db_get();
		}
	}
	
	//Useful for one line method chaining
	public static function factory()
	{
		return new Simple_Cart();
	} 
	
	//Return a static instance of Cart
	public static function instance()
	{
		static $instance;

		empty($instance) and $instance = new Simple_Cart();

		return $instance;
	} 
	
	
	/**
	* add new item to cart 
	*
	* @param   array/object      item
	* @param   int               quantity           
	* @return  void
	*/
	public function cart_add($product, $quantity = 1) 
	{
		$current = $this->get();
		$product = $this->cart_prepare($product);
		$product[$this->config['quantity']] = $quantity;

		if (is_array($current))
		{
			if (array_key_exists($product[$this->config['identifier']],$current)) 
			{
				$product[$this->config['quantity']] = $product[$this->config['quantity']]+$current[$product[$this->config['identifier']]][$this->config['quantity']];
			}
		}
		
		$current[$product[$this->config['identifier']]] = $product;

		$this->set($current);
		
		if (($this->use_db) AND is_object($this->user)) 
		{
			$this->db_set();
		}
	}
	
	/**
	* retrive product from cart 
	*
	* @param   array/object   item     
	* @return  void
	*/
	public function cart_get($product) 
	{
		$current = $this->get();
		if (array_key_exists($product[$this->config['identifier']], $current))
		{ 
			return  $current[$product[$this->config['identifier']]];
		}
	}
	
	/**
	* remove product from cart 
	*
	* @param   array/object   item     
	* @return  void
	*/
	public function cart_delete($product_id) 
	{
		//$product=$this->cart_prepare($product);
		$current = $this->get();
		
		if (array_key_exists($product_id,$current))
		{ 
			unset($current[$product_id]);
		}
		
		$this->set($current); 
		
		if (($this->use_db) AND is_object($this->user))
		{
			$this->db_set();
		}   
	}
	
	/**
	* remove product from cart 
	*
	* @param   array/object   item     
	* @return  void
	*/
	public function cart_remove($product) 
	{
		$product = $this->cart_prepare($product);
		$current = $this->get();
		
		if (array_key_exists($product[$this->config['identifier']], $current))
		{ 
			unset($current[$product[$this->config['identifier']]]);
		}
		
		$this->set($current); 
		
		if (($this->use_db) AND is_object($this->user))
		{
			$this->db_set();
		}    
	}
	
	/**
	* update product in cart 
	*
	* @param   array/object   item
	* @param   int            quantity           
	* @return  void
	*/
	public function cart_update_one($product, $quantity) 
	{
		$current = $this->get();
		$product = $this->cart_prepare($product);
		
		if (array_key_exists($product[$this->config['identifier']], $current))
		{
			if ($quantity>0)
			{ 
				$current[$product[$this->config['identifier']]][$this->config['quantity']] = $quantity;
			}     
			else
			{
				unset($current[$product[$this->config['identifier']]]);
			}
		}
		
		$this->set($current);
		
		if (($this->use_db) AND is_object($this->user))
		{
			$this->db_set();
		}          
	}

/**
	* return cart item from cession 
	*
	* @param   type   description     
	* @return  what
	*/ 
	public function cart_show() 
	{
		$current = $this->get();
		
		if (is_array($current)) 
		{ 
			return $current;
		} 
	}
	
	/**
	* clear cart 
	*    
	* @return  void
	*/
	public function cart_clear() 
	{
	
		if (($this->use_db) AND is_object($this->user)) 
		{
			$this->db_clear();
		} 
	
		return Session::delete($this->config['cart_key']);
	}
	
	/**
	* prepare product, skip not important field, convert object into array 
	*
	* @param   type   description     
	* @return  what
	*/
	public function cart_prepare($product) 
	{
		$cart_data = array();
		if (is_array($product)) 
		{
			foreach ($product as $key=>$value) 
				if (in_array($key,$this->config['fields']))
				{ 
					//$cart_data[$product[$this->config['identifier']]][$key]=$value;    
					$cart_data[$key] = $value;
				}
		} 
		elseif (is_object($product)) 
		{ 
			foreach ($product as $key=>$value)
				if (in_array($key, $this->config['fields'])) {
					//$id_field=$this->config['identifier'];
					//$cart_data[$product->$id_field][$key]=$value;
					$cart_data[$key] = $value;
				}          
		} 
		else 
		{
			return false;
		}
		
		return $cart_data;
	}
	
	//unserialize 
	public function get() {
		return Session::get($this->config['cart_key']);
	}
	
	//serialize
	public function set($data) {
		return Session::set($this->config['cart_key'],$data);
	} 
	
	
	/**
	* update item in cart 
	*
	* @param   array   product_id=>quantity         
	* @return  void
	*/
	public function cart_update($products) {
		$current = $this->get();
		//$product=$this->cart_prepare($product);
		if ((is_array($current)) AND (is_array($products)))
		foreach ($products as $product) 
		if (array_key_exists($product[$this->config['identifier']], $current)) {
				if ($product[$this->config['quantity']]!=0)
				{
					$current[$product[$this->config['identifier']]][$this->config['quantity']] = intval($product[$this->config['quantity']]);
					$current[$product[$this->config['identifier']]]['persons'] = intval($product['persons']);
					//if (intval($product['persons'])===4)
						//$current[$product[$this->config['identifier']]]['price']=$current[$product[$this->config['identifier']]]['price']*2;      
				}      
				else
					unset($current[$product[$this->config['identifier']]]); 
		}
		$this->set($current);
		
		if (($this->use_db) AND is_object($this->user))
			$this->db_set();
	}
	
	/**
	* get cost of cart products
	*        
	* @return  number
	*/
	public function get_cart_cost() {
		$total_cost = 0.00;
		$current = $this->get();
		if (is_array($current))
		foreach ($current as $product) 
		$total_cost = $total_cost + ($product[$this->config['price']] * $product[$this->config['quantity']]); 
		return $total_cost;
	}
	
	
	/**
	* get cost of cart products
	*        
	* @return  number
	*/
	public function count_items() {
		$quantity = 0;
		$current = $this->get();
		if (is_array($current))
		foreach ($current as $product) 
		$quantity = $quantity + $product[$this->config['quantity']]; 
		return $quantity;
	}
	
	
	public function db_set() {
		$user_key = $this->config['db_user'];
		$user_value = $this->user->id;
		$field_name = $this->config['db_data'];
		$data = Simple_Modeler::factory('simple_cart')->load($user_value, $user_key);
		$data->$field_name = serialize($this->get());
		$data->$user_key = $user_value;
		$data->save(); 
	}
	
	public function db_get() {
		$user_key = $this->config['db_user'];
		$user_value = $this->user->id;
		$field_name = $this->config['db_data'];
		$data = Simple_Modeler::factory('simple_cart')->load($user_value, $user_key); 
		if ($data->loaded($field_name))
			$this->set(unserialize($data->$field_name));
	}
	
	public function db_clear() {
		$user_key = $this->config['db_user'];
		$user_value = $this->user->id;
		$field_name = $this->config['db_data'];
		$data = Simple_Modeler::factory('simple_cart')->load($user_value, $user_key); 
		if ($data->loaded())
			$data->delete(); 
	}
}

?>