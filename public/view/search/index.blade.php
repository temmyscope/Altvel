@extends('app')
@section('title', 'Search')
@section('content')

	<?php use App\Helpers\HTML; ?>

	<?= HTML::Card('Search Results'); ?>

	You searched for: <?php print_r(post()->search); ?>
	
@endsection