@extends('app')
@section('title', 'Login')
@section('content')

	<?php use App\Helpers\HTML; ?>

	<?= HTML::Card('Login'); ?>

	<?= HTML::generateForm('login', [
			'email' => [ 'rule' => 'required', 'type'=>'email', 'placeholder' => 'user@example.com'],
			'password' => ['rule' => 'required', 'type' => 'password', 'placeholder' =>'*********'],
			'rememberMe' => ['type' => 'rememberMe'],
			'login' => [ 'type' => 'submit']
		]);
	?>
	<div class='col-md-4 col-form-label text-md-left'>
		<span> <a href="{{ route('login') }}">Login</a> | <a href="{{ route('forgot_password') }}">Forgot password </a> </span>
	</div>
	
@endsection