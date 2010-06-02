<?php 
class User_Articles_Assert implements Acl_Assert_Interface
{

	protected $arguments;

	public function __construct($arguments = array('id'=>'user_id'))
	{
		$this->arguments = $arguments;
	}
	
    public function assert(Acl $acl, $role = null, $resource = null, $privilege = null)
    {
		foreach($this->arguments as $role_key => $resource_key)
		{			
			if($role->$role_key === $resource->$resource_key)
				return TRUE;				
		}
		
		return FALSE;
	}
}