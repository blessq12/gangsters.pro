<h4>О клиенте</h4>
<div style="margin-bottom: 24px">
    <h5>
      <b>Пользователь:</b> {{ $order->user ? 'Авторизован' : 'Не авторизован' }}
    </h5>
    <p>
      <b>Комментарий:</b> {{ $order->comment ?? 'Нет комментария'}}
    </p>
    <p>
      <b>Способ получения : </b> {{ $order->delivery ? 'Доставка' : "Самовывоз" }}
    </p>
    <p>
      <b>Количество персон : </b> {{ $order->personQty }}
    </p>
    @if ($order->user)
        <p>
            Данные пользователя
        </p>
        <ul class="list-unstyled">
            <li>
                Имя: {{ $order->user->name }}
            </li>
            <li>
                Номер телефона: {{ $order->user->tel }}
            </li>
            <li>
                Email адрес: {{ $order->user->email }}
            </li>
        </ul>
    @endif
</div>

<h4>Подробности доставки</h4>
<div style="margin-bottom: 24px">
  <table class="table table-striped">
    <thead>
      <tr>
        <th scope="col">Имя</th>
        <th scope="col">Номер телефона</th>
        <th scope="col">Улица</th>
        <th scope="col">Строение</th>
        <th scope="col">Дом</th>
        <th scope="col">Этаж</th>
        <th scope="col">Подъезд</th>
        <th scope="col">Квартира</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th scope="row">{{ $order->name }}</th>
        <td>
          <a href="tel:{{ $order->tel }}">
              {{ $order->tel }}
          </a>
          </td>
        <td>
          {{ $order->street ?? 'Не указано' }}
        </td>
        <td>
          {{ $order->building ?? 'Не указано' }}
        </td>
        <td>
          {{ $order->house ?? 'Не указано' }}
        </td>
        <td>
          {{ $order->staircase ?? 'Не указано' }}
        </td>
        <td>
          {{ $order->floor ?? 'Не указано' }}
        </td>
        <td>
          {{ $order->apartment ?? 'Не указано' }}
        </td>
      </tr>
    </tbody>
  </table>
</div>
<h4>Подробности заказа</h4>
<div style="margin-bottom: 24px">
  <table class="table table-striped">
    <thead>
      <tr>
        <th scope="col">Наименование</th>
        <th scope="col">Количество</th>
        <th scope="col">Цена</th>
        <th scope="col">Сумма</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($order->items as $item)
        <tr>
          <td>{{ $item->product->name }}</td>
          <td>{{ $item->qty }}</td>
          <td>{{ $item->price }} руб.</td>
          <td>{{ $item->price * $item->qty }} руб.</td>
        </tr>
      @endforeach
    </tbody>
  </table>
<div>
  <h4>Общая стоимость заказа</h4>
  <p>{{ $order->total }} руб.</p>
</div>
</div>
