<?php
namespace App\Providers;

class Application{
	
	public function __construct(){
		$this->_set_ini_setings();
		if ( !app()->get('APP_DEBUG') ) {
			$this->_set_reporting();
		}
		$this->_unregister_globals();
	}

	private function _set_reporting(){
		ini_set("log_errors", TRUE);
		ini_set("error_log", __DIR__.'/../../error_log');
	}

	private function _set_ini_setings(){
		ini_set('zend_extension', 1);
		ini_set('opcache.memory_consumption', 128);
		ini_set('opcache.interned_strings_buffer', 8);
		ini_set('opcache.max_accelerated_files', 4000);
		ini_set('opcache.revalidate_freq', 60);
		ini_set('opcache.fast_shutdown', 1);
		ini_set('opcache.enable_cli', 1);
	}

	private function _unregister_globals(){
		if (ini_get('register_globals')) {
			$globals= ['_SESSION', '_COOKIE', '_POST', '_GET', '_SERVER', '_ENV', '_REQUEST', '_FILES'];
			foreach ($globals as $g) {
				foreach ($GLOBALS[$g] as $key => $value) {
					if ($GLOBALS[$key] === $value) {
						unset($GLOBALS[$key]);
					}
				}
			}
		}
	} 
}