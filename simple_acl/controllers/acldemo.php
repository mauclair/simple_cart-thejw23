<?php 

class AclDemo_Controller extends Controller {
	
	public function __construct() {
		parent::__construct();
		
		echo '<b>See source for usage, use links to verify output</b><br>';
		
		for($i = 1; $i < 13; $i++)
		{
			echo html::anchor('acldemo/demo'.$i,'demo ' . $i),'<br>';
		}
		
		$this->simple_acl = new Simple_Acl;
		$this->simple_acl->add_role('owner');
		$this->simple_acl->add_role('user');
		$this->simple_acl->add_role('guest');
		$this->simple_acl->add_resource('blog');
	}
	
	public function index()
	{
	}
	
	public function demo1()
	{
		// Check on resource while 1 denying privilege is set
		$this->simple_acl->allow('guest','blog');
		$this->simple_acl->deny('guest','blog','read');
		echo ($this->simple_acl->is_allowed('guest','blog','read') ? 'yes' : 'no') . '<br>';
	}
	
	public function demo2()
	{
		// Resource is allowed to all, except owner
		$this->simple_acl->allow(NULL,'blog');
		$this->simple_acl->deny('owner','blog');

		// check for owner
		echo ($this->simple_acl->is_allowed('owner','blog') ? 'yes' : 'no') . '<br>';
		// check for all
		echo ($this->simple_acl->is_allowed(NULL,'blog') ? 'yes' : 'no') . '<br>';
		
		$data = Simple_Modeler::factory('articles',2);

		$user = Simple_Auth::instance()->get_user();
		$this->simple_acl->deny(NULL,'articles');
		$this->simple_acl->allow('user','articles','edit', new User_Articles_Assert); 
		
		echo ($this->simple_acl->allowed($data,'edit') ? 'yes' : 'no') . '<br>';
	}
	
	public function demo3()
	{
		// Owner can do everything but reading
		$this->simple_acl->allow('owner');
		$this->simple_acl->deny('owner',NULL,'read');
		echo ($this->simple_acl->is_allowed(NULL,NULL,'read') ? 'yes' : 'no') . '<br>';
		echo ($this->simple_acl->is_allowed('owner') ? 'yes' : 'no') . '<br>';
		echo ($this->simple_acl->is_allowed('owner',NULL,'read') ? 'yes' : 'no') . '<br>';
		echo ($this->simple_acl->is_allowed('owner','blog','read') ? 'yes' : 'no') . '<br>';
	}
	
	public function demo4()
	{
		$this->simple_acl->allow('owner','blog');
		$this->simple_acl->deny(NULL,'blog');
		echo ($this->simple_acl->is_allowed('owner') ? 'yes' : 'no') . '<br>';
		echo ($this->simple_acl->is_allowed('owner','blog') ? 'yes' : 'no') . '<br>';
		echo ($this->simple_acl->is_allowed('guest','blog') ? 'yes' : 'no') . '<br>';
		echo ($this->simple_acl->is_allowed('guest') ? 'yes' : 'no') . '<br>';
	}
	
	public function demo5()
	{
		$this->simple_acl->allow(NULL,'blog');
		$this->simple_acl->deny('owner','blog','delete');
		echo ($this->simple_acl->is_allowed('owner','blog','read') ? 'yes' : 'no') . '<br>';
		echo ($this->simple_acl->is_allowed('owner','blog','delete') ? 'yes' : 'no') . '<br>';
	}
		

	public function demo6()
	{
		$this->simple_acl->allow(NULL,'blog');
		$this->simple_acl->deny('owner','blog');
		echo ($this->simple_acl->is_allowed(NULL,'blog') ? 'yes' : 'no') . '<br>';
		echo ($this->simple_acl->is_allowed('owner','blog') ? 'yes' : 'no') . '<br>';
	}
	
	public function demo7()
	{
		$this->simple_acl->allow('user','blog');
		echo ($this->simple_acl->is_allowed('user','blog') ? 'yes' : 'no') . '<br>';
		$this->simple_acl->deny('user','blog','read');
		echo ($this->simple_acl->is_allowed('user','blog') ? 'yes' : 'no') . '<br>';
	}
	
	public function demo8()
	{
		// resource inheritance
		$this->simple_acl->add_resource('article','blog');
		$this->simple_acl->allow('user','blog');
		$this->simple_acl->deny('user','article','read');
		echo ($this->simple_acl->is_allowed('user','article') ? 'yes' : 'no') . '<br>';
		
	}

	public function demo9()
	{
		// role inheritance
		$this->simple_acl->add_role('super_user','user');
		$this->simple_acl->allow('user','blog');
		echo ($this->simple_acl->is_allowed('super_user','blog') ? 'yes' : 'no') . '<br>';
		echo ($this->simple_acl->is_allowed('user','blog') ? 'yes' : 'no') . '<br>';
	}
	
	public function demo10()
	{
		// role inheritance, multiple parents
		$this->simple_acl->add_role('super_user',array('user','guest'));
		$this->simple_acl->allow('user','blog');
		echo ($this->simple_acl->is_allowed('super_user','blog') ? 'yes' : 'no') . '<br>';
		echo ($this->simple_acl->is_allowed('user','blog') ? 'yes' : 'no') . '<br>';
		echo ($this->simple_acl->is_allowed('guest','blog') ? 'yes' : 'no') . '<br>';
		
		// conflicting rules to parents
		$this->simple_acl->allow('user','blog','edit');
		$this->simple_acl->deny('guest','blog','edit');

		echo ($this->simple_acl->is_allowed('super_user','blog','edit') ? 'yes' : 'no') . '<br>';		
		echo ($this->simple_acl->is_allowed('user','blog','edit') ? 'yes' : 'no') . '<br>';
		echo ($this->simple_acl->is_allowed('guest','blog','edit') ? 'yes' : 'no') . '<br>';
	}		
	
	public function demo11()
	{
		// Serialization
		$s2 = serialize($this->simple_acl);
		
		echo 'New: ',$s2,'<hr>';
		
		$this->simple_acl = unserialize($s2);
		
		// just run some random other demo to see if the ACLs still function correct
		$this->demo10();

	}			

	public function demo12()
	{
		// Multiple roles - this isn't supported by Zend_ACL, so no comparison to Zend here
		$this->simple_acl = new Acl;
		
		$this->simple_acl->add_resource('forum');
		$this->simple_acl->add_resource('poll');
		
		$this->simple_acl->add_role('forum_manager');
		$this->simple_acl->add_role('poll_manager');
		
		$this->simple_acl->allow('forum_manager','forum','edit');
		$this->simple_acl->allow('poll_manager','poll','edit');
		
		// supply multiple roles in the is_allowed method
		echo ($this->simple_acl->is_allowed(array('forum_manager','poll_manager'),'poll','edit') ? 'yes' : 'no') . '<br>';	

	}			
} 