<?php

use PHPUnit\Framework\TestCase;

use Seven\Router\{Router, str_contains};

class AltvelTest extends TestCase
{

		public function setUp(): void{
        
      $this->router = New Router('App\Controller');

    }

    public function testEndpoints(){
    	$this->router->get('home', function($request, $response){
    		return "home works";
    	});

    	$test = $this->router->run('GET', '/home');

    	$this->assertTrue( str_contains($test, 'home') );
    }
}