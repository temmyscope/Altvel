@extends('app')
@section('title', 'Home')
@section('content')

	You are now logged In:

	<?php 
		dnd($home); 
	?>
	
@endsection