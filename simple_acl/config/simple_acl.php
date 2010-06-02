<?php defined('SYSPATH') OR die('No direct access allowed.');
/*
 * The Authentication library to use
 * Make sure that the library supports:
 * 1) A get_user method that returns FALSE when no user is logged in
 *	  and a user object that implements Acl_Role_Interface when a user is logged in
 * 2) A static instance method to instantiate a Authentication object
 *
 * array(CLASS_NAME,array $arguments)
 */
$config['auth'] = array('Simple_Auth'); // For Kohana's AUTH, simply use array('AUTH');

/*
 * The ACL Roles (String IDs are fine, use of ACL_Role_Interface objects also possible)
 * Use: ROLE => PARENT(S) (make sure parent is defined as role itself before you use it as a parent)
 */
$config['roles'] = array
(
	'user'			=>	null,
	'admin'			=>	'user'
);

/*
 * The name of the guest role 
 * Used when no user is logged in.
 */
$config['guest_role'] = 'guest';

/*
 * The ACL Resources (String IDs are fine, use of ACL_Resource_Interface objects also possible)
 * Use: ROLE => PARENT (make sure parent is defined as resource itself before you use it as a parent)
 */
$config['resources'] = array
(
	'blog'				=>	NULL,
	'articles'			=>	NULL
);

/*
 * The ACL Rules (Again, string IDs are fine, use of ACL_Role/Resource_Interface objects also possible)
 * Split in allow rules and deny rules, one sub-array per rule:
     array( ROLES, RESOURCES, PRIVILEGES, ASSERTION)
 *
 * Assertions are defined as follows :
 		 array(CLASS_NAME,$argument) // (only assertion objects that support (at most) 1 argument are supported
 		                             //  if you need to give your assertion object several arguments, use an array)
 */
$config['rules'] = array
(
	'allow' => array
	(
			// guest can read blog
		array('guest','blog','read'),
		
			// users can add blogs
		array('user','blog','add'),
		
			// users can edit their own blogs (and only their own blogs)
		//array('user','articles','edit',array('Acl_Assert_Argument',array('id'=>'user_id'))),
		
			// administrators can delete everything 
		array('admin','blog','delete'),
	),
	'deny' => array
	(
		  // no deny rules in this example
	)
);