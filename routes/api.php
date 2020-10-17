<?php

$router->use('api-auth;prefix:api;', function() use($router){

		$router->get('home', [ HomeController::class, 'all' ]);

		$router->get('logout', [ AuthController::class, 'logout' ]);

});