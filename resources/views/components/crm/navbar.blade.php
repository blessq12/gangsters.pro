<nav>
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-6">
                <a href="{{ route('main.index') }}">
                    {{ $company->name }}
                </a>
            </div>
            <div class="col-6 d-flex justify-content-end">

                <div class="dropdown">
                    
                    <button class="btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span>{{ Auth::user()->name }}</span>
                        <i class="fa fa-user px-2"></i>
                    </button>
                    <ul class="dropdown-menu">
                      <li>
                        <a class="dropdown-item text-danger" href="{{ route('auth.user-logout') }}">Выйти</a>
                    </li>
                    </ul>
                  </div>
            </div>
        </div>
    </div>
</nav>