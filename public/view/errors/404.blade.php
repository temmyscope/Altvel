@extends('app')
@section('title', 'Error 404')
@section('content')

	<?php use app\Helpers\HTML; ?>

	<?= HTML::Card('Error | 404'); ?>

	Error 404. Resource Not Found.
	
@endsection