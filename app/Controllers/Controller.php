<?php
namespace App\Controllers;

use App\Providers\{Application, Request};

class Controller extends Application{

	public function __construct(){
		parent::__construct();
		$this->request = new Request();
		$this->app = app();
	}

	protected function jsonResponse($resp, $code = 200){
		header("Content-Type: applicaton/json; charset=UTF-8");
		http_response_code($code);
		print_r(json_encode($resp));
	}
}