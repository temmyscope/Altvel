<?php
namespace App\Providers;

use App\Auth;
use \SplFixedArray;

class Request{

	public static $_file, $_instance;
	public $_files = [];
	private $_passed = false, $_errors, $_success, $_warnings= [];

	public function isValid(): bool{
		return (!is_null(post('csrf')) && (string)post('csrf') === (string)Session::get('csrf'))
		 ? true : false;
	}

	public function isSecured(){
		return (self::isNotEmpty() && self::isValid()) ? true : false;
	}

	public function status($type, $msg){
		if('success' === $type) {
			$this->_success[] = $msg;
			Session::set('success', $this->_success);
			return true;
		} elseif('error' === $type) {
			$this->_errors[] = $msg;
			Session::set('errors', $this->_errors);
			return true;
		}elseif ('warning' === $type) {
			$this->_warnings[] = $msg;
			Session::set('warnings', $this->_warnings);
			return true;
		}else{
			$this->_success[] = $msg;
			Session::set('success', $this->_success);
		}
	}

	public function passed(){
		return $this->_passed;
	}

	public function validate($items= [], $table=''){
		$this->_errors = [];
		foreach($items as $item => $rules) {
			$display = $rules['display'];
			$source = $_REQUEST;
			foreach ($rules as $rule => $rule_value) {
				$value = $source[$item];
				if ($rule === 'required' && empty($value)) {
					$this->status('error', ["{$display} is required", $item]);
				}elseif(!empty($value)){
					switch((string) $rule){
						case 'min':
							if (strlen($value) < $rule_value) {
								$this->status('error', ["{$display} must be a minimum of {$rule_value} characters.", $item]);
							}
							break;
						case 'max':
							if (strlen($value) > $rule_value) {
								$this->status('error', ["{$display} must be a maximum of {$rule_value} characters.", $item]);
							}
							break;
						case 'len':
							if (strlen($value) !== $rule_value) {
								$this->status('error', ["{$display} must be exactly {$rule_value} characters.", $item]);
							}
							break;
						case 'matches':
							if ($value != $source[$rule_value]){
								$matchDisplay = $items[$rule_value]['display'];
								$this->status('error', ["{$matchDisplay} and {$display} must match.", $item]);
							}
							break;
						case 'unique':
							if (!empty($table)){
								if (!empty( Auth::setTable($table)->findBy([$item => $value]) )) {
									$this->status('error', ["{$display} already exists. Please choose another {$display}", $item]);
								}
							}else{
								die("\$table is empty. The 'Unique' Validator requires a table to check.");
							}
							break;
						case 'is_numeric':
							if (!is_numeric($value)) {
								$this->status('error', ["{$display} has to be a number. Please use a numeric value.", $item]);
							}
							break;
						case 'valid_email':
							if(!filter_var($value, FILTER_VALIDATE_EMAIL)){
								$this->status('error', ["{$display} must be a valid email address.", $item]);
							}
							break;
						case 'alpha':
							if (!ctype_alpha($value)){
								$this->status('error', ["{$display} can only be alphabeths", $item]);
							}
							break;
						case 'alpha_num':
							if (!ctype_alnum($value)){
								$this->status('error', ["{$display} can only be alphabeths and or numbers.", $item]);
							}
							break;
						case 'is_one_of': 
							if(is_array($rule_value) && (!in_array($value, $rule_value)) ){
								$this->status('error', ["{$display} can only be one of the given options", $item]);
							}
							break;
						case 'equals':
							if( $value !== $rule_value['value'] ){
								$this->status('error', ["{$display} must be the same value as {$rule_value['display']}", $item]);
							}
							break;
						case 'is_same_as':
							if( $value !== $source[$rule_value] ){
								$this->status('error', ["{$display} must be the same value as {$rule_value}", $item]);
							}
							break;
						case 'is_file':
							if (!is_file($value)($value)){
								$this->status('error', ["{$display} must be a valid file type", $item]);
							}
							break;
					}
				}
			}
		}
		if(empty($this->_errors)){
			$this->_passed = true;
		}
		return $this;
	}

	public static function file($var){
		return (isset($_FILES[$var]) && !empty($_FILES[$var])) ? $_FILES[$var] : null;
	}

	public static function has($var){
		return (isset($_REQUEST[$var]) && !empty($_REQUEST[$var])) ? true : false;
	}

	public static function hasFile($var): bool{
		return (isset($_FILES[$var]) && !empty($_FILES[$var])) ? true : false;
	}

	public static function hasFiles(): bool{
		return (isset($_FILES) && !empty($_FILES)) ? true : false;
	}	

	public static function isEmpty(): bool{
		return (empty($_GET) && empty($_POST)) ? true : false ;
	}

	public static function isNotEmpty(): bool{
		return (!empty($_REQUEST)) ? true : false ;
	}
}