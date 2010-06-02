<?php defined('SYSPATH') OR die('No direct access allowed.');

//fields to be included in cart
$config['fields'] = array (
				'id',
				'product_name',
				'price',
				'image');

//unique id column name
$config['identifier'] = 'id';

//quantity column name
$config['quantity'] = 'quantity';

//price column name
$config['price'] = 'price';

//session key name
$config['cart_key'] = 'simple_cart';

//db settings - be sure to check cart model!
//field with user ID
$config['db_user'] = 'user_id';
//field to store cart data
$config['db_data'] = 'simplecart';
