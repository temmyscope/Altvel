<?php
namespace App\Providers;

class Session{
	
	public static function exists($name){
		return (isset($_SESSION[$name])) ? true : false ;
	}

	public static function get($name){
		return $_SESSION[$name] ?? null;
	}

	public static function set($name, $value){
		return $_SESSION[$name] = $value;
	}

	public static function delete($name){
		if(self::exists($name)){
			unset($_SESSION[$name]);
		}
	}

	public static function destroy(){
		$_SESSION = array();
		if(ini_get("session.use_cookies")){
		 	$params = session_get_cookie_params();
		 	setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
		}
		session_destroy();		
	}

	public static function uagent_no_version(){
		$uagent = $_SERVER['HTTP_USER_AGENT'];
		$regx = '/\/[a-zA-Z0-9.]*/';
		$uagent = preg_replace($regx, '', $uagent);
		return $uagent;
	}
}