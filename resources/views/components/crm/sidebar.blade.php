
@php
    $links = [
        (object)['name' => 'Кампания', 'route' => 'crm.companies.index'],
        (object)['name' => 'Заказы', 'route' => 'crm.orders.index'],
        (object)['name' => 'Пользователи', 'route' => 'crm.users.index'],
        (object)['name' => 'Вакансии', 'route' => 'crm.vacancies.index'],
        (object)['name' => 'Товар', 'route' => 'crm.products.index'],
        (object)['name' => 'Истории', 'route' => 'crm.stories.index'],
        (object)['name' => 'Баннеры', 'route' => 'crm.banners.index'],
    ];
@endphp

<div class="sidebar">
    <h4>Навигация</h4>
    <ul>
        @foreach ($links as $link)
            <a href="{{ route($link->route) }}">
                <li class="{{ Request::segment(2) == explode('.', $link->route)[1] ? 'active' : '' }}">{{ $link->name }}</li>
            </a>
        @endforeach
    </ul>
</div>