<div class="admin-bar">
    <div class="container">
        <div class="row">
            <div class="col">
                Привет, {{ Admin::user()->name }}
            </div>
            <div class="col text-end">
                <a href="{{ route('admin.home') }}" class="text-light">
                    <i class="fa fa-sign-in"></i>
                    Войти
                </a>
            </div>
        </div>
    </div>
</div>