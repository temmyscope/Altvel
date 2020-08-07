@extends('app')
@section('title', 'About')
@section('content')

	<?php use App\Helpers\HTML; ?>
	<?= HTML::Card('About Us | Feedback'); ?>

	Welcome To AltVel.

	You'd find all the possible ways to contact us on this page <br>

	Send us an email to: <a href="mailto:Temmyscope@protonmail.com">Temmyscope@protonmail.com</a>  <br>
	Call us on: <a href="tel:2348173295697">08173295697</a>

	<?= HTML::generateForm('about', [
			'email' => [ 'rule' => 'required', 'type'=>'email', 'placeholder' => 'user@example.com'],
			'feedback' => ['rule' => 'required', 'type' => 'textarea', 'placeholder' =>'Write to Us'],
			'Submit' => [ 'type' => 'submit']
		]);
	?>

@endsection