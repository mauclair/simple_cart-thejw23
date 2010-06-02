<?php defined('SYSPATH') or die('No direct script access.');
/**
* Roles Model
*
* @package		Simple_Auth
* @author			thejw23
* @copyright		(c) 2009 thejw23
* @license		http://www.opensource.org/licenses/isc-license.txt
* @version		1.0
* based on KohanaPHP Auth and Auto_Modeler
*/
class Auth_Roles_Model extends Auth_Modeler {

	protected $table_name = 'auth_roles';

	protected $auto_trim = TRUE;

	protected $data = array('id' => '',
						'role' => '',
						'name'=>'');
						
						
						
	/**
	*  Returns an associative array to use in dropdowns
	*
	* @param string $key returned array keys
	* @param string $display returned array values
	* @param string $order_by query ordering
	* @param array $where where conditions
	* @param string $direction query sorting				
	* @return array
	*/
	public function select_list($key, $display, $order_by = NULL, $where = array(), $direction = 'ASC')
	{
		(empty($order_by)) ? $order_by = $this->primary_key : NULL;
		
		$type = $this->where;
		$rows = array();

		if (empty($where))
		{
			//if no where statements, get all records 
			$query = $this->db->select($key,$display)->orderby($order_by,$direction)->get($this->table_name)->result(TRUE, $this->result_object);
		}
		else
		{
			//get using where statement
			$query = $this->db->select($key,$display)->$type($where)->orderby($order_by,$direction)->get($this->table_name)->result(TRUE, $this->result_object);
		}

		foreach ($query as $row)
		{
			//assign key - value for select
			$rows[$row->$key] = $row->$display;
		}

		return $rows;
	}


} // End Roles