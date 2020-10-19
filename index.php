<?php
/*
|-----------------------------------------------------------------------------|
|							USER SESSION STARTS																							|
|-----------------------------------------------------------------------------|
*/
session_start();

use Seven\Router\Router;
use Symfony\Component\HttpFoundation\{Request, Response};
use App\Providers\Session;

/*
|-----------------------------------------------------------------------------|
| Register The Auto Loader 																										|
|-----------------------------------------------------------------------------|
|
*/
require __DIR__.'/vendor/autoload.php';

/*
|-----------------------------------------------------------------------------|
| Load Altvel-Specific Application Object 																		|
|-----------------------------------------------------------------------------|
|
*/

$app = app();

/*
|
|------------------------------------------------------------------------------|
| Load Environment Variables 																									 |
|------------------------------------------------------------------------------|
|
*/
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'DB_DRIVER']);

/*
|
|------------------------------------------------------------------------------|
| Initialize Router And Start Routing Process 																 |
|------------------------------------------------------------------------------|
|
*/
$router = new Router('App\Controllers');

$request = Request::createFromGlobals();

$response = new Response();

//$router->enableCache(__DIR__.'/cache');

$router->registerProviders($request, $response);

$router->middleware('web-auth', function($request, $response, $next){
		if ( !Session::exists('id') ) {
				Session::set('redirect' $request->getPathInfo());
				redirect('login');
		}
		$next($request, $response);
});

$router->middleware('api-auth', function($request, $response, $next){
		$token = $request->headers->get('Authorization');
		if ( !$token || Auth::isValid($token) ) {
				return $response->setContent('Unauthorized.')
				->setStatusCode(401)
				->send();
		}
		$request->userId = Auth::getValuesFromToken($token)->user_id;
		$next->handle($request);
});

require __DIR__.'/routes/web.php';

require __DIR__.'/routes/api.php';

$router->run();