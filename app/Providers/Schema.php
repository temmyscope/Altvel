<?php
namespace App\Providers;

use \PDO;
use \PDOException;

class Schema extends Model{

	public static function init()
	{
		return new self();
	}

	public function db($value)
	{
		try {
			$user = Model::$config[ 'user' ];
			$password = Model::$config[ 'password' ];
			$server = Model::$config[ 'host' ];
			$db = str_replace("pdo_", '', Model::$config[ 'driver' ] );
	    	$conn = new PDO("$db:host=$server;", $user, $password);
	    	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	    	$col = app()->get('ENGINE')['COLLATE'];
	    	$conn->exec("CREATE DATABASE {$value} COLLATE $col;");
	    	self::configureModel($value);
			echo "{$value} database has been created", "\n";
	    }catch(PDOException $e){
		    echo $e->getMessage();
	    }
	}

	public static function configureModel($value)
	{
		$file = __DIR__.'/Model.php';
		file_put_contents($file, implode('', array_map(function($data) use ($value){
			return (strstr($data, "'dbname'")) ? "\t\t'dbname' => '{$value}',\n" : $data;
		}, file($file))));
	}

	public static function run()
	{	
		try {
			$dbname = Model::$config[ 'dbname' ];
			$user = Model::$config[ 'user' ];
			$password = Model::$config[ 'password' ];
			$server = Model::$config[ 'host' ];
			$db = str_replace("pdo_", '', Model::$config[ 'driver' ]);
	    	$conn = new PDO("$db:host=$server;dbname=$dbname", $user, $password);
	    	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	    	$scheme = new MigrationEngine;
	    	self::dropIfExists($scheme, $conn);
	    	self::parserIfExists($scheme, $conn);
	    	self::InsertIfExists($scheme);
	    }catch(PDOException $e){
		    echo $e->getMessage();
	    }
	}

	public function dropIfExists($scheme, $conn)
	{
		$drop = $scheme->drop();
    	if( !empty($drop) ){
    		$sql = "";
	    	foreach ($drop as $key => $value) {
	    		$sql .= "DROP TABLE {$value}; ";
	    	}
	    	$conn->exec($sql);
	    	echo "Tables have been dropped.";
    	}
	}

	protected static function queryMe($sql, $conn)
	{
		try {
			$conn->exec($sql);
	    	echo "Migrations have been registered, generated and run.", "\n";
	    }catch(PDOException $e){
		    echo $e->getMessage();
	    }
	}

	protected static function InsertIfExists($scheme)
	{
		$data = $scheme->populate();
		if (!empty($data)) {
			foreach ($data as $table => $entry) {
				Model::setTable($table)->insert($entry);
				echo "Data has been inserted into ", $table, " table.";
			}
		}
	}

	private static function parserIfExists($scheme, $conn)
	{
		$mig = $scheme->migrate();
		$engine = app()->get('ENGINE');
		$migrable = $engine['MIGRATIONS'];
		if( empty($mig) ){ 
			echo "migrate method in the MigrationEngine Class is empty.";
			return;
		}
		foreach ($mig as $table => $columns) {
			if (in_array($table, $migrable)) {
				$queue = ["ALTER TABLE :table ADD PRIMARY KEY (id);", "ALTER TABLE :table  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;"];
				$_sql = "CREATE TABLE `{$table}` ( id int(11) NOT NULL, ";
				foreach($columns as $key => $value){
					$_sql .= str_replace(":column", $key, $value[0]);
					if($value[1] !== ''){
						$queue[] = str_replace(":column", $key, str_replace(":table", $table, $value[1]) );
					}
				}
				self::queryMe(rtrim($_sql, ', ').");", $conn);
				foreach ($queue as $key => $value) {
					self::queryMe(str_replace(':table', $table, $value), $conn);
				}
			}
		}
	}

	public function integer($max_length=10)
	{
		return [":column int({$max_length}) NOT NULL, ", ""];
	}
	public function double($max_length=10)
	{
		return [ ":column double, ", ""];
	}
	public function float($max_length=10)
	{
		return [ ":column float({$max_length}), ", ""];
	}
	public function string($max_length, $null = false, $key='primary')
	{
		$null = ($null === false) ? "NOT NULL" : "NULL";
		$t = "";
		if(!empty($key)){
			$key = strtolower($key);
			if( $key === 'unique' ){
				$t = "ALTER TABLE :table ADD UNIQUE :column (:column);";
			}elseif( $key === 'fulltext' ){
				$t = "ALTER TABLE :table ADD FULLTEXT KEY :column (:column);";
			} elseif( $key === 'index' ){
				$t = "CREATE INDEX :column ON :table (:column);";
			}
		}
		if ($max_length > 63000) {
			$type =  "text";
		}elseif ($max_length <= 63000) {
			$type = "varchar({$max_length})";
		}elseif ($max_length < 18) {
			$type =  "char({$max_length})" ;
		}
		return [ ":column {$type} {$null}, ", $t ];
	}
	public function oneOf(array $options, $default)
	{
		$options  = implode(', ', $options);
		return [ ":column enum({$options}) NOT NULL DEFAULT {$default}, ", ""];
	}
	public function datetime()
	{
		return [ ":column DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, ", ""];
	}
	public function foreign_key($table, $column, $type=null, $length=10)
	{
		$type  = (strtolower($type) === 'string') ? "varchar({$length})" : 'int ';
		return [":column {$type} NOT NULL,", "ALTER TABLE :table ADD FOREIGN KEY (:column) REFERENCES {$table}({$column});"];
	}
}