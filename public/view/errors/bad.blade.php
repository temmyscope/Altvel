@extends('app')
@section('title', 'Error: Bad Request')
@section('content')
<?php use app\Helpers\HTML; ?>

<?= HTML::Card('Error | Bad Request'); ?>

Bad Request. The server does not know how to handle this request.
@endsection