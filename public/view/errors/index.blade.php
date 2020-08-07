@extends('app')
@section('title', 'Error')
@section('content')

	<?php use app\Helpers\HTML; ?>

	<?= HTML::Card('Error'); ?>

	An Error Occurred. That content does not exist.
	
@endsection