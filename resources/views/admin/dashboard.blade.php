<div class="container-fluid">
    <div class="row mb-4">
        <!-- Статистика -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Заказы за сегодня</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800 d-flex">
                                <i class="fa fa-shopping-cart text-gray-300" style="margin-right: 8px;"></i>
                                {{ $todayOrders->count() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Новые пользователи сегодня
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800 d-flex">
                                <i class="fa fa-users text-gray-300" style="margin-right: 8px;"></i>
                                {{ $todayUsers->count() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Всего товаров
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800 d-flex">
                                <i class="fa fa-archive text-gray-300" style="margin-right: 8px;"></i>
                                {{ $productsCount }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Всего пользователей</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800 d-flex">
                                <i class="fa fa-user text-gray-300" style="margin-right: 8px;"></i>
                                {{ $usersCount }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Таблица заказов за сегодня -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Заказы за сегодня</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Имя клиента</th>
                                    <th>Телефон</th>
                                    <th>Сумма</th>
                                    <th>Статус</th>
                                    <th>Время</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($todayOrders as $order)
                                <tr>
                                    <td>{{ $order->id }}</td>
                                    <td>{{ $order->name }}</td>
                                    <td>{{ $order->tel }}</td>
                                    <td>{{ $order->total }} ₽</td>
                                    <td>
                                        @switch($order->status)
                                            @case(1)
                                                <span class="badge badge-primary">Новый</span>
                                                @break
                                            @case(10)
                                                <span class="badge badge-success">Выполнен</span>
                                                @break
                                            @case(11)
                                                <span class="badge badge-danger">Списан</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>{{ $order->created_at->format('H:i') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Таблица новых пользователей -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Новые пользователи за сегодня</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Имя</th>
                                    <th>Email</th>
                                    <th>Телефон</th>
                                    <th>Время регистрации</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($todayUsers as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->tel }}</td>
                                    <td>{{ $user->created_at->format('H:i') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>