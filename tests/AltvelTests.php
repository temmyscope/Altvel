<?php
use PHPUnit\Framework\TestCase;

use Seven\Router\Router;

class AltvelTests extends TestCase
{

		public function setUp(): void{
        
        $this->router = new Router('App\Controller');
        $router = $this->router;
        $this->send = function($method, $route) use ($router){
        	return $router->run($method, $route);
        };
    }



//all user defined test classes must extend this one
}