<?php
namespace App\Providers;

use Seven\Model\ModelTrait;

class Model
{
	use ModelTrait;

	/**
	* This variable is extremely essential to the proper functioning of the trait due to the underlying Doctrine DBAL package  
	*/
	protected static $config = [
		'dbname' => 'seven',
		'user' => 'root',
		'password' => '',
		'host' => 'localhost',
	    'driver' => 'pdo_mysql'
	];

}