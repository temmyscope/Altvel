@extends('app')
@section('title', 'Error: Access Denied')
@section('content')

	<?php use app\Helpers\HTML; ?>

	<?= HTML::Card('Error | Access Denied'); ?>

	Access Denied. You do not have admin permission to access that page

@endsection