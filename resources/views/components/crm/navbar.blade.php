<nav>
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-6">
                <a href="{{ route('main.index') }}" class="text-decoration-none fw-bold text-dark">
                    {{ $company->name }}
                </a>
            </div>
            <div class="col-6 d-flex justify-content-end">

                <div class="dropdown">
                    {{-- user --}}
                    <button class="btn borde border-dark" type="button" data-bs-toggle="dropdown" style="margin-right: 6px">
                        <i class="fa fa-user"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item text-danger" href="{{ route('auth.user-logout') }}">Выйти</a>
                        </li>
                    </ul>
                    {{-- mmenu --}}
                    
                </div>
                <crm-mobile-menu :links='@json($crmLinks)'></crm-mobile-menu>
            </div>
        </div>
    </div>
</nav>