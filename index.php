<?php
/*
|-----------------------------------------------------------------------------|
|                            USER SESSION STARTS                           		|
|-----------------------------------------------------------------------------|
*/
session_start();

use Seven\Router\Router;
use Symfony\Component\HttpFoundation\{Request, Response};
use App\Providers\Session;
use App\Auth;

/*
|-----------------------------------------------------------------------------|
| Register The Auto Loader                                                    |
|-----------------------------------------------------------------------------|
|
*/

$loader = require __DIR__ . '/vendor/autoload.php';

#For adding namespaces
$loader->add('App\Http', __DIR__.'/app/Http/');

/*
| You don't need to do anything here
|-----------------------------------------------------------------------------|
| Load Altvel-Specific Application Object                                     |
|-----------------------------------------------------------------------------|
|
*/

$app = new App\Providers\Application();
$request = $app->request();
$response = $app->response();
# $session = $app->session();

# $cookie = $app->cookie();

/*
|
|------------------------------------------------------------------------------|
| Load Environment Variables                                                   |
|------------------------------------------------------------------------------|
|
*/

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'DB_DRIVER']);

/*
|
|------------------------------------------------------------------------------|
| Initialize Router And Start Routing Process                                  |
|------------------------------------------------------------------------------|
|
*/

$router = New Router($namespace = 'App\Controllers');

//$router->enableCache(__DIR__.'/cache');

$router->registerProviders($request, $response);

$router->middleware('web-auth', function ($request, $response, $next) use ($app) {
    if ( !$app->session->exists('id') ) {
        $app->session->set('redirect', $_SERVER['PATH_INFO']);
        redirect('login');
    }
    $next($request, $response);
});

$router->middleware('api-auth', function ($request, $response, $next) {
    $token = $request->headers->get('Authorization');
    if (!$token || !Auth::isValid($token)) {
        return $response->setContent('Unauthorized.')
            ->setStatusCode(401)
            ->send();
    }
    $request->userId = Auth::getValuesFromToken($token)->user_id;
    $next->handle($request);
});

require __DIR__ . '/routes/web.php';

$router->run($_SERVER['REQUEST_METHOD'], $_SERVER['PATH_INFO'] ?? '/');
