<?php
namespace App\Providers;

use App\Auth;
use \DI;

class Router{

	public static function route($cache_dir){
		$url = (isset($_SERVER['PATH_INFO'])) ? explode('/', $_SERVER['PATH_INFO']) : []; 
		array_shift($url);

		$app = app();
		$controllers = $app->get('controllers');
		$def_controller = $app->get('DEFAULT_CONTROLLER');
		$user_id = $app->get('CURRENT_USER_SESSION_NAME');
		$rdr = $app->get('redirect');
		$cookie = $app->get('remember_me');

		if ( isset($url[0]) && !empty($url[0]) ) {

			if ( isset($controllers[$def_controller]) && in_array( strtolower( $url[0] ), $controllers[$def_controller] ) ) {
				$controller = $def_controller;
				$_endpoint = $url[0].'EndPoint';
				array_shift($url);
			}elseif ( isset($controllers['RESTRICTED'][$def_controller]) && in_array( strtolower( $url[0] ), $controllers['RESTRICTED'][$def_controller] ) ) {
				$controller = $def_controller;
				$_endpoint = $url[0].'EndPoint';
				array_shift($url);
			}elseif ( in_array( strtolower( $url[0] ), $controllers['AuthController']) ) {
				$controller = 'AuthController';
				$_endpoint = $url[0].'EndPoint';
				array_shift($url);
			} else {
				$controller = ucfirst(substr_replace($url[0], '', strcspn($url[0], '.'))).'Controller';
				array_shift($url);
				if(array_key_exists($controller, $controllers)){
					if ( isset($url[0]) && $url[0] != '' && in_array($url[0], $controllers[$controller]) ) {
						$_endpoint = $url[0].'EndPoint';
						array_shift($url);
					} else {
						$_endpoint = 'indexEndPoint';
					}
				}elseif(array_key_exists($controller, $controllers['RESTRICTED'])){
					if ( isset($url[0]) && $url[0] != '' && in_array($url[0], $controllers['RESTRICTED'][$controller]) ) {
						$_endpoint = $url[0].'EndPoint';
						array_shift($url);
					} else {
						$_endpoint = 'indexEndPoint';
					}
					
					if(!Session::exists($user_id) && !Cookie::exists($cookie)){
						$request = [];
						$request [] = str_replace('Controller', '', $controller);
						$request [] = str_replace('EndPoint', '', $_endpoint);
						if ($_endpoint == 'indexEndPoint') {
							array_pop($request);
						}
						if( isset($url) && !empty($url) ){
							foreach ($url as $key => $value) {
								$request [] = $value;
							}
						}
						Session::set($rdr, implode('/', $request) );
						unset($request);
						self::redirect('login');
					}
				}else{
					$controller = 'ErrorsController';
					$_endpoint = 'indexEndPoint';
				}
			}
		} else {
			$controller = $def_controller;
			$_endpoint = 'indexEndPoint';
		}

		if(!Session::exists($user_id) && Cookie::exists($cookie)){
		    Auth::loginUserFromCookie();
		}
		if(Session::exists($user_id) && 
			$controller == 'AuthController' && ($_endpoint != 'indexEndPoint' && $_endpoint != 'logoutEndPoint' && $_endpoint != 'aboutEndPoint')){
			self::redirect('home');
		}
		$queryParams = sanitize($url);
		$controller = 'App\Controllers\\' . $controller;

		if(method_exists($controller, $_endpoint)){
			$builder = new DI\ContainerBuilder();
			$builder->enableCompilation($cache_dir . '/tmp');
			$builder->writeProxiesToFile(true, $cache_dir . '/tmp/proxies');
			$builder->useAnnotations(false);
			$container = $builder->build();
			$container->call([$controller, $_endpoint], $queryParams);
		}else{
			self::redirect('errors');
		}
	}

	public static function redirect($location){
		$location = app()->get('APP_URL'). "/{$location}";
		if(!headers_sent()){ header("location: $location"); exit();
		}else{
			echo "<script type='text/javascript'> window.location.href= '{$location}';</script>";
			echo '<noscript> <meta http-equiv="refresh" content="0;url='.$location.'"/></noscript>'; exit();
		}
	}

	public static function getRedirect(){
		$rdr = app()->get('REDIRECT');
		if (Session::exists($rdr)) {
			$route = Session::get($rdr);
			Session::delete($rdr);
			self::redirect($route);
		}else{
			self::redirect('home');
		}
	}
}
?>