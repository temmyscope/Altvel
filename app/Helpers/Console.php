<?php
namespace App\Helpers;
 
use App\Providers\{Strings, Schema};

class Console{

	public static function parse($argc, $argv){
		print("Welcome To The Altvel Framework Engineer Console:\n\n");
		@$method = explode('::', $argv[0])[1];
		if (is_callable([ self::class, $method ])){
			array_shift($argv);
			return self::$method($argv);
		}else{
			echo "Engineer Console Error: Invalid Syntax.\n","For help, Enter: php Engineer app::help";
		}
	}

	public static function start($argv)
	{
		$project = $argv[0] ?? self::receiveInput();

		self::configureApp(strtolower($project));
	}

	public static function init($argv)
	{
		return self::start($argv);
	}

	public static function controller($argv)
	{
		$name = $argv[0] ?? self::receiveInput('controller');
		$name = strtolower(str_ireplace('controller', '', $name));
		$nm = ucfirst($name);
		self::writeToFile(
			ROOT.DS."App".DS."Controllers",
			"{$nm}Controller.php",
			"<?php \nnamespace App\Controllers;\n\nuse App\Providers\Strings;\n\nclass {$nm}Controller extends Controller{\n\n\tpublic function IndexEndPoint(){ \n\t\tview('{$name}.index'); \n\t}\n}"
		);
		self::generateModel($name);
		print("{$nm}Controller and corresponding Model has been generated.\n\n");
	}

	public static function view($argv)
	{
		$name = $argv[0] ?? self::receiveInput('view');
		$fileName = $argv[1] ?? "index";
		$nm = ucfirst($name);
		self::writeToFile(
			ROOT.DS.'public'.DS.'view'.DS.$name,
			$fileName.".blade.php",
			"@extends('app')\n@section('title', '{$nm}')\n@section('content')\n\n\t<?php use App\Helpers\HTML; ?> \n\n\t<?= HTML::Card('{$nm}'); ?>\n\tThis is the {$nm} landing page\n\n@endsection"
		);
		print("{$nm} view has been generated.\n\n");
	}

	public static function model($argv)
	{
		$name = $argv[0] ?? self::receiveInput('model');
		$table = strtolower($name);
		$nm = str_replace(" ", "", ucwords(str_replace('_', " ", $name)) );
		$fileName = $nm.".php";
		self::writeToFile(
			ROOT.DS."app",
			$fileName,
			"<?php \nnamespace App;\n\nuse App\Providers\Model; \n\nclass {$nm} extends Model{\n\n\tprotected static \$table = '{$table}'; \n\n\tprotected static \$fulltext = []; \n\n\tprotected static \$fetchable = [];\n\n}"
		);
	}

	public static function db($argv)
	{
		$db = $argv[0] ?? self::receiveInput();
		return Schema::db(strtolower($db));
	}

	public static function migrate($argv)
	{
		Schema::run();
		foreach (app()->get('ENGINE')['MIGRATIONS'] as $value) {
			self::generateModel($value);
		}
		return;
	}

	public static function writeToFile($directory, $fileName, $content)
	{
		if(!is_dir($directory)){
			mkdir($directory);
		}
		if( file_exists($directory.DS.$fileName) ){
			echo"File already exists";
			return;
		}
		$fh = fopen($directory.DS.$fileName, 'w+');
		fwrite($fh, $content);
		fclose($fh);
		return;
	}

	public static function receiveInput($var='project')
	{
		echo "Enter your {$var} name:\n";
		$fp = fopen('php://stdin', 'r');
		$data = fgets($fp, 1024);
		if (!ctype_alnum($data)) {
			echo "Only Alpha-numeric characters are allowed.";
			exit;
		}
		return $data;
	}

	public function help(){
		print("To start a new app and generate secured keys for your Altvel app, use:\n\t");
		print("\"php Engineer App::start {{app_name}}\" \n\n");
		echo "To generate a controller: \n\t \"php Engineer App::Controller controller_name\" \n\n";
		echo "To generate a model: \n\t \"php Engineer App::Model model_name\" \n\n";
		echo "To create a database: \n\t \"php Engineer App::db {{ db }}\" \n\n";
		echo "To create a migration: \n\t \"php Engineer App::migrate\" \n\n";
	}

	public static function configureApp($name){
		$vw = "<?php \nreturn [
	#App settings
	'APP_NAME' => '{$name}',
	'APP_DEBUG' => true,
	'APP_URL' => 'http://localhost/{$name}',
	'APP_CDN' => 'http://localhost/{$name}/cdn',
	'APP_ROOT' => __DIR__.'/..',
	'APP_PUSH_ICON' => '', //file address to app push notification icon

	#Mail
	'APP_EMAIL' => '',
	
	#Sessions & Cookies
	'CURRENT_USER_SESSION_NAME' => '',
	'REMEMBER_ME_COOKIE_NAME' => '',
	'REMEMBER_ME_COOKIE_EXPIRY' => 2592000,
	'REDIRECT' => '',


	#Files, Filesystem and Storage Upload Settings
	'cdn' => __DIR__.'/../public/cdn',
	'view' => __DIR__.'/../public/view',
	'assets' => 'http://localhost/{$name}/public/assets',
	'cache' => __DIR__.'/../cache',
	'upload_limit' => 5024768,
	'allowed_files' => [ 
		'jpg' => 'image/jpeg',
		'png' => 'image/png', 
		'jpeg' => 'image/jpeg'
	],


	#Database Migration Settings
	'ENGINE' => [
		'TYPE' => 'SQL',
		'CHARSET' => 'utf8mb4',
		'COLLATE' => 'utf8mb4_unicode_ci',
		'GENERATE_MODEL' => true,
		'MIGRATIONS' => [ 'users', 'user_sessions', 'contact_us' ],
	],

	#Html Templates
	/*----------------------------------------------------------------------------------------------|
	|								ALTVEL/LARAFELL NAVIGATION BAR																									|
	|-----------------------------------------------------------------------------------------------|
	|	this helps in setting the menu bar for guest users and loggged in users based on the array 		|
	|	associative arrays can be used for menus with dropdown... 																		| 
	-----------------------------------------------------------------------------------------------*/

	'USER_NAVBAR' => ['Home' => 'home', 'Search' => 'search', 'Logout' => 'logout'],
	'GUEST_NAVBAR' => ['Login' => 'login', 'Register' => 'register', 'About' => 'about'],

];";
		self::writeToFile(ROOT.DS.'config', 'app.php', $vw);
		$config = ROOT.DS.'config'.DS.'app.php';
		self::configureTITLE($config, $name);
		self::configureFOLDER($config, $name);
		self::configureREDIRECT($config);
		self::configureCOOKIE($config);
		self::configureSESSION($config);
		self::configureSALT(ROOT.DS.'..'.DS.'..'.DS.'.env');

		print("Environment Security Configurations have been successfully set up.\n");
		print("Please rename your root folder (i.e. your current folder) to {$name}. \n");
		print("You may still have to manually setup some configurations for a production\nserver in the config/app.php file of your application.\n");
		exit();
	}

	public static function configureTITLE($file, $title){
		file_put_contents($file, implode('', array_map(function($data) use ($title){
			return (strstr($data, "'APP_NAME'")) ? "\t'APP_NAME' => '{$title}',\n" : $data;
		}, file($file))));
		file_put_contents($file, implode('', array_map(function($data) use ($title){
			return (strstr($data, "'APP_URL'")) ? "\t'APP_URL' => 'http://localhost/{$title}',\n" : $data;
		}, file($file))));
		file_put_contents($file, implode('', array_map(function($data) use ($title){
			return (strstr($data, "'APP_CDN'")) ? "\t'APP_CDN' => 'http://localhost/{$title}/cdn',\n" : $data;
		}, file($file))));
	}

	public static function configureFOLDER($file, $brand){
		file_put_contents($file, implode('', array_map(function($data) use ($brand){
			return (strstr($data, "'APP_ROOT'")) ? "\t'APP_ROOT' => __DIR__.'/..',\n" : $data;
		}, file($file))));
	}

	public static function configureREDIRECT($file){ 
		file_put_contents($file, implode('', array_map(function($data){
			$const = Strings::Rand(32);
			return (strstr($data, "'REDIRECT'")) ? "\t'REDIRECT' => '{$const}',\n" : $data;
		}, file($file))));
	}

	public static function configureCOOKIE($file){
		file_put_contents($file, implode('', array_map(function($data){
			$const = Strings::Rand(32);
			return (strstr($data, "'REMEMBER_ME_COOKIE_NAME'")) ? "\t'REMEMBER_ME_COOKIE_NAME' => '{$const}',\n" : $data;
		}, file($file))));
	}

	public static function configureSESSION($file){
		file_put_contents($file, implode('', array_map(function($data){
			$const = Strings::Rand(32);
			return (strstr($data, "'CURRENT_USER_SESSION_NAME'")) ? "\t'CURRENT_USER_SESSION_NAME' => '{$const}',\n" : $data;
		}, file($file))));
	}

	public static function configureSALT($file){
		$ciphers = openssl_get_cipher_methods();
		$vulnerables = [ "ecb", "des", "rc2", "rc4", "md5"];
		foreach ($vulnerables as $key => $value) {
			$ciphers = array_filter( $ciphers, function($n) { return stripos($n, $value)===FALSE; } );
		}
		$ciphers = array_values($ciphers); $limit = count($ciphers)-1;
		$cipher = $ciphers[ random_int(0, $limit )];

		file_put_contents($file, implode('', array_map(function($data) use ($cipher){
			$iv = base64_encode( openssl_random_pseudo_bytes( openssl_cipher_iv_length($cipher) ));
			return (strstr($data, "APP_IV")) ? "\tAPP_IV='{$iv}'\n" : $data;
		}, file($file))));

		file_put_contents($file, implode('', array_map(function($data) use ($cipher){
			return (strstr($data, "APP_ALG")) ? "\tAPP_ALG='{$cipher}'\n" : $data;
		}, file($file))));

		file_put_contents($file, implode('', array_map(function($data){
			$const = Strings::Rand(32);
			return (strstr($data, "APP_SALT")) ? "\tAPP_SALT='{$const}'\n" : $data;
		}, file($file))));
	}
}