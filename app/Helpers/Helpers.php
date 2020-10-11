<?php

use Seven\Vars\Strings;
use Jenssegers\Blade\Blade;

function curl($url)
{
	return new class($url){
        protected $_curl = [
            'url' => '',
			'data' => [],
			'headers' => [],
            'time_out' => 200,
            'cookie_file' => '',
            'cookie_jar' => '',
            'method' => 'GET',
            'ret' => true,
        ];
        protected $_result, $_errors;
		function __construct($url){
			$this->_curl['url'] = filter_var($url, FILTER_SANITIZE_URL);
        }
		public function setData(array $postdata){
            $this->_curl['data'] = json_encode($postdata);
			return $this;
		}
		public function setHeaders($headers)
		{
			$this->_curl['headers'] = $headers;
			return $this;	
		}
		public function setHeader($headers)
		{
			return $this->setHeaders($headers);
		}
		public function setSession($cookiefile){
            $this->_curl['cookie_file'] = $cookiefile;
			return $this;
        }
		public function saveSession($cookiefile){
            $this->_curl['cookie_jar'] = $cookiefile;
			return $this;
        }
		public function setMethod(string $method){
            $this->_curl['method'] = strtoupper($method);
			return $this;
        }
		public function isReturnable(bool $val = true){
            $this->_curl['ret'] = $val;
			return $this;
        }
		public function setTimeOut($time = 200){
            $this->_curl['time_out'] = $time;
			return $this;
        }
		public function send(){
			array_push($this->_curl['headers'], 'Content-Type: application/json');
            $ch = curl_init($this->_curl['url']);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($this->_curl['method']) );
            if ( !empty($this->_curl['data']) ) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_curl['data']);
            }
            if (!empty($this->_curl['cookie_jar']) && !empty($this->_curl['cookie_file']) ) {
                curl_setopt($ch, CURLOPT_COOKIEJAR, $this->_curl['cookie_jar'] );
                curl_setopt($ch, CURLOPT_COOKIEFILE, $this->_curl['cookie_file'] );
            }
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, $this->_curl['ret']);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->_curl['time_out'] );
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->_curl['time_out'] );
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_curl['headers']);
			$this->_result = curl_exec($ch);
			$this->_errors = curl_error($ch);
			curl_close($ch);
			//dnd($this->_result);
			if ( $this->_errors ) {
				return false;
			} else {
				return $this->_result;
			}
        }
		public function result()
		{
			return $this->_result;
        }
		public function errors()
		{
			return $this->_errors;
        }
	};
}

function app($config = __DIR__.'/../../config/app.php')
{
	return new class($config){
		public function __construct($config){
			$this->config = require $config;
		}
		public function __call($method, $args){
			return $this->config[ strtolower($method) ] ?? null;
		}

		public function get(string $var)
		{
			return $this->config[$var] ?? null;
		}

		public function all()
		{
			return $this->config;
		}
	};
}

function sanitize($dirty){
	$clean_input = [];
    if(is_array($dirty)){
        foreach ($dirty as $k => $v) {
            $clean_input[$k] = htmlentities($v, ENT_QUOTES, 'UTF-8');
        }
    } else {
        $clean_input = htmlentities($dirty, ENT_QUOTES, 'UTF-8');
    }
    return $clean_input;
}

function dnd($var){
	echo "<pre>";
		var_dump($var);
	echo "<pre>";
	die();
}

function resume(){
	return getRedirect();
}

function redirect($location){
	$location = app()->get('APP_URL'). "/{$location}";
	if(!headers_sent()){ header("location: $location"); exit();
	}else{
		echo "<script type='text/javascript'> window.location.href= '{$location}';</script>";
		echo '<noscript> <meta http-equiv="refresh" content="0;url='.$location.'"/></noscript>'; exit();
	}
}

function getRedirect(){
		$rdr = app()->get('REDIRECT');
		if (Session::exists($rdr)) {
			$route = Session::get($rdr);
			Session::delete($rdr);
			self::redirect($route);
		}else{
			self::redirect('home');
		}
}

function pusher($tokens = [], string $msg)
{
		$msg = [ 
			'title' => app()->get('APP_NAME')." Notification",
			'body'	=> $msg,
			'icon'	=> app->get('APP_PUSH_ICON')
		];
		return curl('https://fcm.googleapis.com/fcm/send')
			->setMethod('POST')->setHeaders([ 
				'Authorization: key='.app()->get('firebase_token'), 
				'Content-Type: Application/json' 
			])->setData(['registration_ids' => $tokens_array, 'data' => $msg ])
			->send();
}

function mailer($email, $subject, $message){
		$headers = implode("\r\n", [
			'From: '. app()->get('APP_NAME') .' Team',
			'Reply-To: '. app()->get('app_email'),
			'MIME-Version: 1.0',
			'Content-Type: text/html; charset=UTF-8',
			'X-Priority: 3',
			'nX-MSmail-Priority: high'
		]);
		if(mail($email, $subject, $message, $headers)){
			return true;
		}
}

function status(){
	if(App\Providers\Session::exists('errors')){
	    $html = "<div><ul class='alert alert-danger'>";
	    $errors = App\Providers\Session::get('errors');
	    foreach($errors as $error){
	    	if(is_array($error)){
	    		$html .= '<li style="list-style: none;text-align: center; color: white;">'.$error[0].'</li><br/>';	
	    	}else{
	    		$html .= '<li style="list-style: none;text-align: center; color: white;">'.$error.'</li><br/>';	
	    	}
	    }
	    $html .= '</ul></div>';
	    App\Providers\Session::delete('errors');
	    return $html;	
	}
	if(App\Providers\Session::exists('warnings')){
		$html = "<div><ul class='alert alert-warning'>";
	    $errors = App\Providers\Session::get('warnings');
	    foreach($errors as $error) {
	    	if (is_array($error)) {
	    		$html .= '<li style="list-style: none;text-align: center; color: white;">'.$error[0].'</li><br/>';	
	    	}else{
	    		$html .= '<li style="list-style: none;text-align: center; color: white;">'.$error.'</li><br/>';	
	    	}
	    }
	    $html .= '</ul></div>';
	    App\Providers\Session::delete('warnings');
	    return $html;
	}
	if(App\Providers\Session::exists('success')){
		$html = "<div><ul class='alert alert-success'>";
	    $errors = App\Providers\Session::get('success');
	    foreach($errors as $error){
	    	if(is_array($error)){
	    		$html .= '<li style="list-style: none;text-align: center; color: white;">'.$error[0].'</li><br/>';	
	    	}else{
	    		$html .= '<li style="list-style: none;text-align: center; color: white;">'.$error.'</li><br/>';	
	    	}
	    }
	    $html .= '</ul></div>';
	    App\Providers\Session::delete('success');
	    return $html;
	}
}

function errors(){
	if(App\Providers\Session::exists('errors')){
	    $html = "<div><ul class='alert alert-danger'>";
	    $errors = App\Providers\Session::get('_errors');
	    foreach($errors as $error){
	    	if(is_array($error)){
	    		$html .= '<li style="list-style: none;text-align: center; color: white;">'.$error[0].'</li><br/>';	
	    	}else{
	    		$html .= '<li style="list-style: none;text-align: center; color: white;">'.$error.'</li><br/>';	
	    	}
	    }
	    $html .= '</ul></div>';
	    App\Providers\Session::delete('errors');
	    return $html;	
	}
}


/**
*	@param formats may vary e.g. controllerName@endpoint; controllerName.endpoint; controllerName/endpoint; 
*/
function route($var): string{
	$var = str_replace('@', '/', $var);
	$var = str_replace('.', '/', $var);
	$var = str_ireplace('controller', '', $var);
	return app()->get('APP_URL').'/'.$var;
}

function app_url(): string{
	return app()->get('APP_URL');
}

function view($view, $data = []): void
{
	$v = new class() extends Blade{

		public function __construct(){
			parent::__construct( app()->get('view'), app()->get('cache') );
		}
		public function rend($viewName, $data = []){
			try {
				echo $this->render($viewName, [ 'dataSource' => $data ]);
			} catch (Exception $e) {
				echo $e->getMessage();
			}
		}
	};
	$v->rend($view, $data);
}

function get($var = ''){
	if(!empty($var)){
		return (isset($_GET[$var])) ? sanitize($_GET[$var]) : null;	
	}else{
		return (!empty($_GET)) ? (object) sanitize($_GET) : null;
	}
}

function post($var = ''){
	if(!empty($var)){
		return (isset($_POST[$var])) ? sanitize($_POST[$var]) : null;
	}else{
		return (!empty($_POST)) ? (object) sanitize($_POST) : null;
	}
}

function request($var = ''){
	if(!empty($var)){
		return (isset($_REQUEST[$var]) && !empty($_REQUEST[$var])) ? sanitize($_REQUEST[$var]) : null;
	}
	return (!empty($_REQUEST)) ? (object) sanitize($_REQUEST) : null;
}

function destroy_request(): bool{
	$_GET = $_POST = $_REQUEST = $_FILES = [];
	return true;
}

/*--------------------------------------------------------------
|	$arr1 = [
|			'function'=> 'strpos',
|			'parameters'=> ['home.php', '.']
|	]; 
	$arr2 = [
|			'function'=> 'strstr',
|			'parameters'=> ['home.php', '.']
|	];
|	speed_cmp($arr1, $arr2);
----------------------------------------------------------------*/
function speed_cmp(...$args){
	if(count($args) > 1){
		foreach($args as $key => $value){
			$time_start= microtime(true);
			$mem_start = memory_get_usage(true);
			for ($i=0; $i <= 10000; $i++) { 
				call_user_func_array($args[$key]['function'], $args[$key]['parameters']);
			}
			$mem_end = memory_get_usage(true);
			$time_end= microtime(true);
			$time_elapsed= $time_end - $time_start;
			$memory_used = $mem_end - $mem_start;
			echo "<pre>";
			echo "Time elapsed for testcase <b>{$key}</b> is {$time_elapsed}";
			echo "Memory used for testcase <b>{$key}</b> is {$memory_used}";
			echo "<pre>";
		}
	}else{
		throw new Exception("Testcases must be atleast 2", 1);
	}
}