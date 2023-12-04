@extends('layouts.auth')

@section('title', 'Регистрация')

@section('content')
    <div class="d-flex align-items-center" style="min-height: 100vh">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-md-6 col-lg-5">
                    <h4>Регистрация</h4>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="list-unstyled p-0 m-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form action="{{ route('auth.user-register') }}" method="post" class="border border-primary rounded p-3">
                        @csrf
                        <div class="form-group">
                            <label for="email">Email</label>
                            <div class="input-group">
                                <input type="email" name="email" id="email" class="form-control" autocomplete="username" required>
                                <span class="input-group-text">@</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="name">Имя</label>
                            <div class="input-group">
                                <input type="text" name="name" id="name" class="form-control" autocomplete="additional-name" required>
                                <span class="input-group-text">
                                    <i class="fa fa-user"></i>
                                </span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="tel">Телефон</label>
                            <div class="input-group">
                                <input type="text" name="tel" id="tel" class="form-control" autocomplete="tel" data-maska="+7 (###) ###-##-##" required>
                                <span class="input-group-text">
                                    <i class="fa fa-phone"></i>
                                </span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="password">Пароль</label>
                            <div class="input-group">
                                <input type="password" name="password" id="password" class="form-control" autocomplete="new-password" required>
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
                            <button type="submit" class="btn btn-primary">Регистрация</button>
                            <a href="{{ route('auth.user-login') }}" class="btn btn-outline-primary">Авторизация</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        const { Mask, MaskInput, vMaska } = Maska
        new MaskInput("[data-maska]")
    </script>
@endsection