<?php
namespace App\Controllers;

class ErrorsController extends Controller{
	
	public function IndexEndPoint(){
		view('errors.index'); //error 204... content does not exist
	}

	public function _404EndPoint(){
		view('errors.404');
	}

	public function _405EndPoint(){
		view('errors.405'); //endpoint not allowed
	}

	public function BadEndPoint(){
		view('errors.bad');   //error 400... bad request
	}

	public function UnknownEndPoint(){
		view('errors.unknown'); //sth went wrong
	}

	public function DeniedEndPoint(){
		view('errors.denied'); //error 403... permission denied
	}
}