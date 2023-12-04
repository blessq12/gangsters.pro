@extends('layouts.auth')

@section('title', 'Авторизация')

@section('content')
    <div class="d-flex align-items-center" style="min-height: 100vh">
    
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-md-6 col-lg-5">
                    <h4>Авторизация</h4>
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="list-unstyled p-0 m-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form action="{{ route('auth.user-login') }}" method="post" class="border border-primary rounded p-3">
                        @csrf
                        <div class="form-group">
                            <label for="email">Введите логин</label>
                            <div class="input-group">
                                <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required autocomplete="username">
                                <span class="input-group-text">@</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="password">Введите пароль</label>
                            <div class="input-group">
                                <input type="password" name="password" id="password" class="form-control" required autocomplete="current-password">
                                <span 
                                    class="input-group-text" 
                                    style="cursor: pointer"
                                    onclick="document.querySelector('#password').type == 'password' ? document.querySelector('#password').type = 'text' : document.querySelector('#password').type = 'password'"
                                >
                                    <i class="fa fa-eye"></i>
                                </span>
                            </div>
                        </div>
                        <div class="form-group mt-3">
                            <button type="submit" class="btn btn-primary">
                                Войти
                            </button>
                            <a href="{{ route('auth.register-page') }}" class="btn btn-outline-primary">Регистрация</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection