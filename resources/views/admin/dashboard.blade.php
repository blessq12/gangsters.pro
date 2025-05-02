<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<script src="https://cdn.tailwindcss.com/3.4.3"></script>

<div class="min-h-screen bg-gray-50 py-10 px-6 sm:px-8 lg:px-10">
    <div class="max-w-8xl mx-auto">
        <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4 mb-10">
            <!-- Заказы за сегодня -->
            <div class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-shadow duration-300 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-500 rounded-xl p-4">
                            <i class="fas fa-shopping-cart text-white text-3xl"></i>
                        </div>
                        <div class="ml-6">
                            <p class="text-base font-medium text-gray-500">Заказы за сегодня</p>
                            <p class="text-3xl font-bold text-gray-900">{{ $totalTodayOrders }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Новые пользователи -->
            <div class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-shadow duration-300 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-500 rounded-xl p-4">
                            <i class="fas fa-users text-white text-3xl"></i>
                        </div>
                        <div class="ml-6">
                            <p class="text-base font-medium text-gray-500">Новые пользователи</p>
                            <p class="text-3xl font-bold text-gray-900">{{ $totalTodayUsers }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Всего товаров -->
            <div class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-shadow duration-300 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-indigo-500 rounded-xl p-4">
                            <i class="fas fa-archive text-white text-3xl"></i>
                        </div>
                        <div class="ml-6">
                            <p class="text-base font-medium text-gray-500">Всего товаров</p>
                            <p class="text-3xl font-bold text-gray-900">{{ $productsCount }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Всего пользователей -->
            <div class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-shadow duration-300 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-500 rounded-xl p-4">
                            <i class="fas fa-user text-white text-3xl"></i>
                        </div>
                        <div class="ml-6">
                            <p class="text-base font-medium text-gray-500">Всего пользователей</p>
                            <p class="text-3xl font-bold text-gray-900">{{ $usersCount }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Яндекс Метрика -->
        <div class="bg-white rounded-2xl shadow-sm p-8 mb-10">
            <h2 class="text-2xl font-semibold text-gray-900 mb-6">Статистика Яндекс.Метрики</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="bg-gray-50 rounded-xl p-6 hover:bg-gray-100 transition-colors duration-200">
                    <div class="flex items-center">
                        <i class="fas fa-eye text-blue-500 text-2xl mr-4"></i>
                        <div>
                            <p class="text-base text-gray-500">Просмотров страницы</p>
                            <p class="text-xl font-semibold">{{ $yaMetrika->pageviews }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-xl p-6 hover:bg-gray-100 transition-colors duration-200">
                    <div class="flex items-center">
                        <i class="fas fa-chart-line text-green-500 text-2xl mr-4"></i>
                        <div>
                            <p class="text-base text-gray-500">Посещений</p>
                            <p class="text-xl font-semibold">{{ $yaMetrika->visits }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-xl p-6 hover:bg-gray-100 transition-colors duration-200">
                    <div class="flex items-center">
                        <i class="fas fa-users text-indigo-500 text-2xl mr-4"></i>
                        <div>
                            <p class="text-base text-gray-500">Пользователей</p>
                            <p class="text-xl font-semibold">{{ $yaMetrika->users }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-xl p-6 hover:bg-gray-100 transition-colors duration-200">
                    <div class="flex items-center">
                        <i class="fas fa-clock text-purple-500 text-2xl mr-4"></i>
                        <div>
                            <p class="text-base text-gray-500">Среднее время на сайте</p>
                            <p class="text-xl font-semibold">{{ $yaMetrika->avg_time_on_site }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-xl p-6 hover:bg-gray-100 transition-colors duration-200">
                    <div class="flex items-center">
                        <i class="fas fa-layer-group text-yellow-500 text-2xl mr-4"></i>
                        <div>
                            <p class="text-base text-gray-500">Глубина просмотра</p>
                            <p class="text-xl font-semibold">{{ $yaMetrika->page_depth }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-xl p-6 hover:bg-gray-100 transition-colors duration-200">
                    <div class="flex items-center">
                        <i class="fas fa-sign-out-alt text-red-500 text-2xl mr-4"></i>
                        <div>
                            <p class="text-base text-gray-500">Процент отказа</p>
                            <p class="text-xl font-semibold">{{ $yaMetrika->bounce_rate }}%</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Источники трафика -->
            <div class="mt-8">
                <h3 class="text-xl font-semibold text-gray-900 mb-6">Источники трафика</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-gray-50 rounded-xl p-6 hover:bg-gray-100 transition-colors duration-200">
                        <div class="flex items-center">
                            <i class="fas fa-link text-blue-500 text-2xl mr-4"></i>
                            <div>
                                <p class="text-base text-gray-500">Прямые заходы</p>
                                <p class="text-xl font-semibold">{{ $yaMetrika->sources['direct'] }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-6 hover:bg-gray-100 transition-colors duration-200">
                        <div class="flex items-center">
                            <i class="fas fa-search text-green-500 text-2xl mr-4"></i>
                            <div>
                                <p class="text-base text-gray-500">Поисковые заходы</p>
                                <p class="text-xl font-semibold">{{ $yaMetrika->sources['search'] }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-6 hover:bg-gray-100 transition-colors duration-200">
                        <div class="flex items-center">
                            <i class="fas fa-share-alt text-purple-500 text-2xl mr-4"></i>
                            <div>
                                <p class="text-base text-gray-500">Из соц сетей</p>
                                <p class="text-xl font-semibold">{{ $yaMetrika->sources['social'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Сумма заказов -->
        <div class="bg-white rounded-2xl shadow-sm p-8 mb-10">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-500 rounded-xl p-4">
                    <i class="fas fa-money-bill-wave text-white text-3xl"></i>
                </div>
                <div class="ml-6">
                    <p class="text-base font-medium text-gray-500">Сумма заказов за сегодня</p>
                    <p class="text-4xl font-bold text-gray-900">{{ $totalTodayRevenue }} ₽</p>
                </div>
            </div>
        </div>

        <!-- Таблица заказов -->
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden mb-10">
            <div class="px-8 py-6 border-b border-gray-200">
                <h3 class="text-2xl font-semibold text-gray-900">Заказы за сегодня</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-8 py-4 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">
                                ID</th>
                            <th class="px-8 py-4 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Имя клиента</th>
                            <th class="px-8 py-4 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Телефон</th>
                            <th class="px-8 py-4 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Сумма</th>
                            <th class="px-8 py-4 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Статус</th>
                            <th class="px-8 py-4 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Время</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($todayOrders as $order)
                            <tr class="hover:bg-gray-50 transition-colors duration-200">
                                <td class="px-8 py-5 whitespace-nowrap text-base text-gray-900">{{ $order->id }}
                                </td>
                                <td class="px-8 py-5 whitespace-nowrap text-base text-gray-900">{{ $order->name }}
                                </td>
                                <td class="px-8 py-5 whitespace-nowrap text-base text-gray-900">{{ $order->tel }}
                                </td>
                                <td class="px-8 py-5 whitespace-nowrap text-base text-gray-900">{{ $order->total }} ₽
                                </td>
                                <td class="px-8 py-5 whitespace-nowrap">
                                    @switch($order->status)
                                        @case(1)
                                            <span
                                                class="inline-flex items-center px-4 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                                Новый
                                            </span>
                                        @break

                                        @case(10)
                                            <span
                                                class="inline-flex items-center px-4 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                                Выполнен
                                            </span>
                                        @break

                                        @case(11)
                                            <span
                                                class="inline-flex items-center px-4 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                                Списан
                                            </span>
                                        @break
                                    @endswitch
                                </td>
                                <td class="px-8 py-5 whitespace-nowrap text-base text-gray-900">
                                    {{ $order->created_at->format('H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Таблица пользователей -->
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-8 py-6 border-b border-gray-200">
                <h3 class="text-2xl font-semibold text-gray-900">Новые пользователи за сегодня</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-8 py-4 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">
                                ID</th>
                            <th class="px-8 py-4 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Имя</th>
                            <th class="px-8 py-4 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Email</th>
                            <th class="px-8 py-4 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Телефон</th>
                            <th class="px-8 py-4 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">
                                Время регистрации</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($todayUsers as $user)
                            <tr class="hover:bg-gray-50 transition-colors duration-200">
                                <td class="px-8 py-5 whitespace-nowrap text-base text-gray-900">{{ $user->id }}
                                </td>
                                <td class="px-8 py-5 whitespace-nowrap text-base text-gray-900">{{ $user->name }}
                                </td>
                                <td class="px-8 py-5 whitespace-nowrap text-base text-gray-900">{{ $user->email }}
                                </td>
                                <td class="px-8 py-5 whitespace-nowrap text-base text-gray-900">{{ $user->tel }}
                                </td>
                                <td class="px-8 py-5 whitespace-nowrap text-base text-gray-900">
                                    {{ $user->created_at->format('H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
