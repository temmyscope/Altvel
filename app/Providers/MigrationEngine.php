<?php
namespace App\Providers;

class MigrationEngine extends Schema{

	public function migrate()
	{
		return [/*
			'users' => [
				'name' => $this->string($max_length=125, $null=false),
				'email' => $this->string($max_length=125, $null=false, $key='unique'),
				'password' => $this->string($max_length=125),
				'backup_pass' => $this->string($max_length=150),
				'activation' => $this->string($max_length=225),
				'verified' => $this->oneOf($options=["'true'", "'false'"], $default="'false'" ),
				'created_at' => $this->datetime(),
				'deleted' => $this->oneOf($options=["'true'", "'false'"], $default="'false'" )
			],
			'contact_us' => [
				'name' => $this->string($max_length=125, $null=false),
				'email' => $this->string($max_length=125),
				'feedback' => $this->string($max_length=1025),
				'created_at' => $this->datetime(),
				'deleted' => $this->oneOf($options=["'true'", "'false'"], $default="'false'" )
			],
			'user_sessions' => [
				'user_id' => $this->foreign_key($table='users', $column='id', $type = 'int' ),
				'session' => $this->string($max_length=225, $null=false),
				'user_agent' => $this->string($max_length=225, $null=false),
				'push_token' => $this->string($max_length=225, $null=false),
				'created_at' => $this->datetime(),
				'deleted' => $this->oneOf($options=["'true'", "'false'"], $default="'false'" )
			],
			*/
		];
	}

	public function populate()
	{
		return [
			
		];
	}

	public function drop()
	{
		return [
			//'users', 'user_sessions', 'contact_us'
		];
	}

}