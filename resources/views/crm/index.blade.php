@extends('layouts.crm')

@section('title', 'Дашборд')

@section('content')
    <div class="container py-4">
        <div class="row">
            <div class="col-12">
                <a href="{{ route('auth.user-logout') }}" class="btn btn-danger">выйти</a>
            </div>
        </div>
    </div>
@endsection