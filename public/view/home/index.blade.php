@extends('app')
@section('title', 'Home')
@section('content')

	<?php use App\Helpers\HTML; ?>

	<?= HTML::Card('Home'); ?>
	You are now logged In
	
@endsection