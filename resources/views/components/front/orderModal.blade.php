<table class="table table-striped">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">Название</th>
      <th scope="col">Количество</th>
      <th scope="col">Цена</th>
      <th scope="col">Итог</th>
    </tr>
  </thead>
  <tbody>
    @foreach ($order->items as $item)
        <tr>
            <th scope="row">{{ $item->id }}</th>
            <td>{{ $item->product->name }}</td>
            <td>{{ $item->qty }} шт.</td>
            <td>{{ $item->price }} руб.</td>
            <td>{{ $item->price * $item->qty }} руб.</td>
        </tr>
    @endforeach
  </tbody>
</table>
<hr>
<h4 class="fw-semibold">
    Итого: {{ $order->total }} руб.
</h4>