<?php
/**
* Simple_Modeler
*
* @package		Simple_Modeler
* @author			thejw23
* @copyright		(c) 2009 thejw23
* @license		http://www.opensource.org/licenses/isc-license.txt
* @version		1.6.2.1
* @last change		a lot :)
* 				instance() fix
* 				reset()
* 				save() and empty $data_to_save fix
* 				save() and set id after insert fix
* 				__construct, added load_columns() 
* 				ID hashing, every row can have sha1 crypted id (with additional table name and secret phrase) 
*         timestamp fix
*         save() modification - it now assign data to data_original (insead of only setting the id)
* 
* @NOTICE			table columns should be different from class varibales/methods names
* @NOTICE			ie. having table column 'timestamp' or 'skip' may (and probably will) lead to problems
*  
* modified version of Auto_Modeler by Jeremy Bush, 
* class name changed to prevent conflicts while using original Auto_Modeler 
*/
class Simple_Modeler_Core extends Model {
	// The database table name
	protected $table_name = '';
	
	//primary key for the table
	protected $primary_key = 'id';
	
	//id hash field
	protected $hash_field = '';
	protected $hash_suffix = '';
	 	
	//if true all fields will be trimmed before save
	protected $auto_trim = FALSE;

	// store single record database fields and values
	protected $data = Array();
	protected $data_original = Array();
		
	// array, 'form field name' => 'database field name'
	public $aliases = Array(); 

	// skip those fields from save to database
	public $skip = Array ();

	// timestamp fields, they will be auto updated on db update
	// update is only if table has a column with given name
	public $timestamp = Array('time_stamp');
	
	//timestamp fields updated only on db insert
	public $timestamp_created = Array('time_stamp_created');

	//type of where statement: where, orwhere, like, orlike...
	public $where = 'where';
	
	//fetch only those fields, if empty select all fields
	public $select;

	//array with offset and limit for limiting query result
	public $limit;
	public $offset = 0; 
	
	//db result object type
	public $result_object = 'stdClass'; //defaults, arrays: MYSQL_ASSOC objects: stdClass 

	/**
	* Constructor
	*
	* @param integer|array $id unique record to be loaded	
	* @return void
	*/
	public function __construct($id = FALSE)
	{
		parent::__construct();

		if ($id != FALSE)
		{
			$this->load($id);
		}
		
		$this->load_columns();   
	}
		
	/**
	* Return a static instance of Simple_Modeler.
	* Useful for one line method chaining.	
	*
	* @param string $model name of the model class to be created
	* @param integer|array $id unique record to be loaded	
	* @return object
	*/
	public static function factory($model = FALSE, $id = FALSE)
	{
		$model = empty($model) ? __CLASS__ : ucwords($model).'_Model';
		return new $model($id);
	}
	
	/**
	* Create an instance of Simple_Modeler.
	* Useful for one line method chaining.	
	*
	* @param string $model name of the model class to be created
	* @param integer|array $id unique record to be loaded	
	* @return object
	*/
	public static function instance($model = FALSE, $id = FALSE)
	{
		static $instance;
		$model = empty($model) ? __CLASS__ : ucwords($model).'_Model';
		// Load the Simple_Modeler instance
		empty($instance) and $instance = new $model($id);
		
		//make sure that instance reflect passed model		
		if ( ! $instance instanceof $model)
			return  new $model($id);
			 
		return $instance;
	}
	
	/**
	* Generates user fiendly $data array with table columns	
	*
	* @return string
	*/
	public function generate_data() 
	{
		$out = "";
		
		//get columns from table
		$columns = $this->explain();
		
		if (!empty($columns))
		{
			$out .= '<pre>table: '.$this->table_name."<br />";
			$out .= 'protected $data = array(<br />';
			foreach ($columns as $column => $type)
			{
				$out .= "\t\t'".$column."' => '',<br />"; 
			}
			//remove last comma
			$out = rtrim($out,',<br />');
			$out .= "<br />\t\t);</pre>";	
		}
		
		//return formatted html code
		return $out;
	}

	/**
	* Shows table name of the loaded model
	*	
	* @return string
	*/
	public function get_table_name() 
	{
		return $this->table_name;
	}

	/**
	*  Allows for setting data fields in bulk	
	*
	* @param array $data data passed to $data
	* @return object
	*/
	public function set_fields($data)
	{
		//assign new valuse to current data
		foreach ($data as $key => $value)
		{
			$key = $this->check_alias($key);

			if (array_key_exists($key, $this->data))
			{
				//skip field not existing in current table
				($this->auto_trim) ? $this->data[$key] = trim($value) : $this->data[$key] = $value;
			}
		}
		
		return $this;
	}

	/**
	*  Saves the current $data to DB	
	*
	* @return mixed
	*/
	public function save()
	{
		$data_to_save = array_diff_assoc($this->data, $this->data_original);

		// if no changes, quit
		if (empty($data_to_save))
		{
			return NULL;
		}

		$data_to_save = $this->check_timestamp($data_to_save, $this->loaded());
		$data_to_save = $this->check_skip($data_to_save);

		// Do an update
		if ($this->loaded())
		{ 
				$count = count($this->db->update($this->table_name, $data_to_save, array($this->primary_key => $this->data[$this->primary_key])));
				if ($count) 
				{
					$this->data_original = $this->data;
					return $count; 
				} 
		}
		else // Do an insert
		{
			$id = $this->db->insert($this->table_name, $data_to_save)->insert_id();
			if ($id)
			{
				$this->data[$this->primary_key] = $id;
				$this->data_original = $this->data;
			}
			
			if ($id AND !empty($this->hash_field))
			{
				$this->db->update($this->table_name, array($this->hash_field => sha1($this->table_name.$id.$this->hash_suffix)), array($this->primary_key => $id));
			}
			
			return ($id);
		}
		return NULL;
	}
	
	/**
	*  Set the DB results object type	
	*
	* @param string $object type or returned object
	* @return object
	*/
	public function set_result($object = stdClass) 
	{
		$this->result_object = $object;
		return $this; 
	}
	
	//reset settings
	public function reset()
	{
		$this->where = 'where';
		$this->select = '';
		$this->limit = '';
		$this->offset = 0; 
		$this->result_object = 'stdClass';
		return $this; 
	}
	
	/**
	* load single record based on unique field value	
	*
	* @param array|integer $value column value
	* @param string $key column name  	 
	* @return object
	*/
	public function load($value, $key = NULL)
	{
		(empty($key)) ? $key = $this->primary_key : NULL;

		$type = $this->where;
		
			//get data
			//if value is an array, make where statement and load data
			if (is_array($value))
			{
				$data = $this->db->select($this->select)->$type($value)->get($this->table_name)->result(TRUE);
			}
			else //else load by default ID key
			{
				$data = $this->db->select($this->select)->$type(array($key => $value))->get($this->table_name)->result(TRUE);
			}

			// try and assign the data
			if (count($data) === 1 AND $data = $data->current())
			{
				// set original data
				$this->data_original = (array) $data;
				// set current data
				$this->data = $this->data_original; 
			}
		
			return $this;
	}
	
	/**
	*  Returns single record without using $data		
	*
	* @param array|integer $value column value
	* @param string $key column name  	
	* @return mixed
	*/
	public function fetch_row($value, $key = NULL) 
	{
		(empty($key)) ? $key = $this->primary_key : NULL;

		$type = $this->where;
				
			// get data
			//if value is an array, make where statement and load data
			if (is_array($value))
			{
				$data = $this->db->select($this->select)->$type($value)->get($this->table_name)->result(TRUE, $this->result_object);
			}
			else //else load by default ID key
			{
				$data = $this->db->select($this->select)->$type(array($key => $value))->get($this->table_name)->result(TRUE, $this->result_object);
			}

			// try and assign the data
			if (count($data) === 1 AND $data = $data->current())
			{				
				return $data;
			}

			return NULL;
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
			//set timestamp fields
			$data_to_save = $this->check_timestamp($data_to_save);

			//remove skipped fields
			$data_to_save = $this->check_skip($data_to_save);

			//insert data and get inserted id
			$id = $this->db->insert($this->table_name, $data_to_save)->insert_id();
			return $id;
		}
	}

	/**
	*  Update table	
	*
	* @param array $data_to_update data to update
	* @param array $where where condition	
	* @return mixed
	*/
	public function update($data_to_update = array(), $where = array()) 
	{
		if ( ! empty($data_to_update) AND is_array($data_to_update) AND  ! empty($where) AND is_array($where))
		{
			//set timestamp fields
			$data_to_update = $this->check_timestamp($data_to_update, TRUE);

			//remove skipped fields
			$data_to_update = $this->check_skip($data_to_update);

			//update table and get number of changed records
			$changed = $this->db->update($this->table_name, $data_to_update, $where);
			return $changed;
		}
	}

	/**
	* Deletes from db current record or condition based records 	
	*
	* @param array $what data to be deleted
	* @return mixed
	*/ 
	public function delete($what = array())
	{
		//delete by conditions
		if (( ! empty($what)) AND (is_array($what)))
		{
			//delete  based on passed conditions
			return $this->db->delete($this->table_name, $what);
		}
		//else delete current record
		elseif (intval($this->data[$this->primary_key]) !== 0) 
		{
			//if no conditions and data is loaded -  delete current loaded data by ID
			return $this->db->delete($this->table_name, array($this->primary_key => $this->data[$this->primary_key]));
		}
	}

	/**
	*  Fetches all records from the table	
	*
	* @param string $order_by ordering
	* @param string $direction sorting	
	* @return mixed
	*/
	public function fetch_all($order_by = NULL, $direction = 'ASC')
	{
		(empty($order_by)) ? $order_by = $this->primary_key : NULL;
		
			//if there are limits
			if ( ! empty($this->limit)) 
			{
				return $this->db->select($this->select)->limit($this->limit,$this->offset)->orderby($order_by, $direction)->get($this->table_name)->result(TRUE, $this->result_object);
			}
			//else get all records from table
			else
			{
				return $this->db->select($this->select)->orderby($order_by, $direction)->get($this->table_name)->result(TRUE, $this->result_object);
			}
		
		return NULL;
	} 
	
	/**
	*  Fetches some records from the table	
	*
	* @param array $where where conditions	
	* @param string $order_by ordering
	* @param string $direction sorting	
	* @return mixed
	*/
	public function fetch_where($where = array(), $order_by = NULL, $direction = 'ASC')
	{	
		(empty($order_by)) ? $order_by = $this->primary_key : NULL;

		//assign where type (like, where, orwhere...)
		$type = $this->where;
			
			//if fetch with limits	
			if ( ! empty($this->limit)) 
			{
				return $this->db->select($this->select)->$type($where)->limit($this->limit,$this->offset)->orderby($order_by, $direction)->get($this->table_name)->result(TRUE, $this->result_object);
			}
			//else get all records from table based on passed conditions
			else
			{ 
				return $this->db->select($this->select)->$type($where)->orderby($order_by, $direction)->get($this->table_name)->result(TRUE, $this->result_object);
			}
		
		return NULL;
	}

	/**
	*  Run query on DB	
	*
	* @param string $sql query to be run
	* @return object
	*/
	public function query($sql)
	{
		return $this->db->query($sql)->result(TRUE, $this->result_object);
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
	 
	/**
	*  Checks if given key is a timestamp and should be updated	
	*
	* @param string $key key to be checked
	* @return array
	*/
	 public function check_timestamp($data, $loaded = FALSE)
	 {
		//update timestamp fields with current datetime
		if ($loaded)
		{
			if ( ! empty($this->timestamp) AND is_array($this->timestamp))
				foreach ($this->timestamp as $field)
					if (array_key_exists($field, $this->data_original)) 
					{
						$data[$field] = date('Y-m-d H:i:s');
					}
		}
		//new record is created
		else 
		{
			if ( ! empty($this->timestamp_created) AND is_array($this->timestamp_created))
				foreach ($this->timestamp_created as $field)
					if (array_key_exists($field, $this->data_original))
					{
						$data[$field] = date('Y-m-d H:i:s');
					}
		}
		return $data;
	 }
	 
	/**
	*  Checks if given key should be skipped	
	*
	* @param array $data data to be checked
	* @return object
	*/
	 public function check_skip($data)
	 {
		if ( ! empty($this->skip) AND is_array($this->skip))
			foreach ($this->skip as $skip)
				if (array_key_exists($skip, $data))
				{ 
					unset($data[$skip]);
				}
				
		return $data;
	 }
	
	/**
	*  Set where statement	
	*
	* @param string $where query where
	* @return object
	*/
	public function where($where = NULL)
	{
		if ( ! empty($where))
		{
			$this->where = $where;
		}

		return $this;
	}

	/**
	*  Set columns for select
	*
	* @param array $fields query select
	* @return object
	*/
	public function select($fields = array())
	{
		if (empty($fields)) 
			return $this;

		if (is_array($fields))
		{
			$this->select = $fields;
		}
		elseif(func_num_args() > 0)
		{
			$this->select = func_get_args();
		}

		return $this;
	} 

	/**
	*  Set limits for select	
	*
	* @param integer $limit query limit
	* @param integer $offset query offset	
	* @return object
	*/
	public function limit($limit, $offset = 0)
	{
		if (intval($limit) !== 0)
		{
			$this->limit = intval($limit);
			$this->offset = intval($offset);
		}
		return $this;
	}
	
	/**
	*  shortcut for easier count all records	
	*
	* @return integer
	*/
	public function count_all() 
	{
		return $this->db->count_records($this->table_name);
	}

	/**
	*  shortcut for easier count limited records	
	*
	* @param array $fields query where condition
	* @return integer
	*/
	public function count_where($fields = array()) 
	{
		$type=$this->where;
		return $this->db->$type($fields)->count_records($this->table_name);
	}

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
			$query = $this->db->select($key,$display)->orderby($order_by,$direction)->get($this->table_name)->result(TRUE);
		}
		else
		{
			//get using where statement
			$query = $this->db->select($key,$display)->$type($where)->orderby($order_by,$direction)->get($this->table_name)->result(TRUE);
		}

		foreach ($query as $row)
		{
			//assign key - value for select
			$rows[$row->$key] = $row->$display;
		}

		return $rows;
	}
	
	/**
	*  check if data has been retrived from db and has a primary key value other than 0	
	*
	* @param string $field data key to be checked
	* @return boolean
	*/	
	public function loaded($field = NULL) 
	{
		(empty($field)) ? $field = $this->primary_key : NULL;
		return (intval($this->data[$field]) !== 0) ? TRUE : FALSE;
	}

	/**
	*  check if data has been modified	
	*
	* @return boolean
	*/
	public function diff() 
	{
		return ($this->data === $this->data_original) ? TRUE : FALSE;
	}
	
	/**
	*  clear values of $data and $data_original 	
	*
	* @return void
	*/
	public function clear_data()
	{
		array_fill_keys($this->data, '');
		array_fill_keys($this->data_original, '');
	}

	/**
	*  load table fields into $data	
	*
	* @return void
	*/
	public function load_columns() 
	{
		//only if table_name is set and there are no columns set
		if ( ! empty($this->table_name) AND (empty($this->data_original)) )
		{
			//only if auto_fields are enabled
			if (! IN_PRODUCTION AND $this->auto_fields)  
			{
				//load from DB
				$columns = $this->explain();
	
				$this->data = $columns;
				$this->data_original = $this->data;
			}
			else // rise an error? 
			{
				Kohana::log('alert', 'Simple_Modeler, IN_PRODUCTION is TRUE and there is empty $data for table: '.$this->table_name);
			}
		}

		if ( ! empty($this->data) AND (empty($this->data_original)) )
			foreach ($this->data as $key => $value) 
			{
				$this->data_original[$key] = '';
			}
	}
	
	/**
	*  get table columns from db	
	*
	* @return array
	*/ 
	public function explain()
	{
		//get columns from database
		$columns = array_keys($this->db->list_fields($this->table_name, TRUE));
		$data = array();

		//assign default empty values
		foreach ($columns as $column) 
		{ 
			$data[$column] = '';
		}
		return $data;
	}
	
	/**
	*  return current loaded data	
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
	*  magic set to $data	
	*
	* @param string $key key to be modified
	* @param string $value value to be set
	* @return object
	*/
	public function __set($key, $value)
	{
		$key = $this->check_alias($key);

		if (array_key_exists($key, $this->data) AND (empty($this->data[$key]) OR $this->data[$key] !== $value))
		{
			return ($this->auto_trim) ? $this->data[$key] = trim($value) : $this->data[$key] = $value;
		}
		return NULL;
	}

	/**
	*  serialize only needed values (without DB connection)	
	*
	* @return array
	*/
	public function __sleep()
	{
		// Store only information about the object
		return array('skip','aliases','timestamp','timestamp_created','table_name','data_original','data','primary_key','where','limit','offset','select','auto_trim','result_object');
	}

	/**
	*  unserialize	
	*
	* @return void
	*/
	public function __wakeup()
	{
		// Initialize database
		parent::__construct();
	}

}