<?php
namespace App\Providers;

class Cookie{
	
	public static function exists($name){
		return (isset($_COOKIE[$name])) ? true : false ;
	}

	public static function get($name){
		return $_COOKIE[$name] ?? null;
	}

	public static function set($name, $value){
		$time = time() + (app()->get('REMEMBER_ME_COOKIE_EXPIRY'));
		if(setcookie($name, $value, $time, '/')){
			return true;
		}
		return false;
	}

	public static function delete($name){
		self::set($name, '', time()-3600);
	}
}