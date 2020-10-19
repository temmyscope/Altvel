<?php
namespace App\Providers;

use Seven\Vars\{Strings, Validation};

class Application{
	
	public function __construct(){
		if (!getenv('APP_DEBUG')) {
			$this->setLogger();
		}
		$this->string = new Strings(getenv('APP_ALG'), getenv('APP_SALT'), getenv('APP_IV') );
	}

	private function setLogger(){
		ini_set("log_errors", TRUE);
		ini_set("error_log", __DIR__.'/../../error.log');
	}

	public function decrypt(string $str)
	{
		return $this->string->decrypt($str);
	}

	public function encrypt(string $str)
	{
		return $this->string->encrypt($str);
	}

	public function config()
	{
		return new class(){
			public function __construct(){
				$this->config = require __DIR__.'/../../config/app.php';
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

	public function time(string $str = 'now'){
		return $this->string->time_from_string($str, $this->config()->get('APP_TIMEZONE'));
	}

	public function validate(array $entries)
	{
		
	}

}