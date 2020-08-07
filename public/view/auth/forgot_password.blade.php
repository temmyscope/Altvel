@extends('app')
@section('title', 'Forgot Password')
@section('content')

	<?php use App\Helpers\HTML; ?>

	<?= HTML::Card('Forgot Password'); ?>

	<?= HTML::generateForm('forgot_password', [
				'email' => [ 'rule' => 'required', 'type' => 'email', 'placeholder' => 'user@example.com'],
				'Regenerate Password' => [ 'type' => 'submit']
			]);
	?>
@endsection