<?php
namespace App\Controllers;

use App\Providers\Application;
use Seven\Vars\Validation;

class Controller extends Application{

		public function __construct(){
				parent::__construct();
				$this->app = app();
		}

}