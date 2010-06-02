<?php defined('SYSPATH') or die('No direct script access.');

class Simple_Cart_Model  extends Simple_Modeler {
	// Database table name to store cart content
	protected $table_name = 'cart';
	
	protected $data = array('id' => '',
						'user_id' => '',
						'simplecart' => ''
						);
	
	
}
