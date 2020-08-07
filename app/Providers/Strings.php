<?php
namespace App\Providers;

use Seven\Vars\Strings as StringsParent;

class Strings extends StringsParent{

	public function __construct()
	{
		$app = app();
		parent::__construct($app->get('APP_ALG'), $app->get('APP_SALT'), $app->get('APP_IV') );
	}
}