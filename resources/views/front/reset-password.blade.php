@extends('layouts.front')

@section('title', 'Сброс пароля')

@section('content')
    <reset-form :token="{{ json_encode($token) }}"></reset-form>
@endsection
