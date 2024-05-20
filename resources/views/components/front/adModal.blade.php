<div style="margin-bottom: 24px">
    <h5>
        Пользователь: {{ $order->user ? 'Авторизован' : 'Не авторизован' }}
    </h5>
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

<table class="table table-striped">
  <thead>
    <tr>
      <th scope="col">Имя</th>
      <th scope="col">Номер телефона</th>
      <th scope="col">Доставка</th>
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
            <b>
                {{ $order->delivery? 'Доставка' : 'Самовывоз' }}
            </b>
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
