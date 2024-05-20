<table class="table table-striped">
  <thead>
    <tr>
      <th scope="col">ID</th>
      <th scope="col">Название</th>
      <th scope="col">Вес (нетто)</th>
      <th scope="col">Цена</th>
    </tr>
  </thead>
  <tbody>
    @foreach ($category->products as $product)
    <tr>
        <th scope="row">{{ $product->id }}</th>
        <td>{{ $product->name }}</td>
        <td>{{ $product->weight }}</td>
        <td>{{ $product->price }}</td>
    </tr>
    @endforeach
  </tbody>
</table>