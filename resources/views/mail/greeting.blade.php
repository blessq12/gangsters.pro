<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</head>
<body>
    <div class="container">
        <div class="row mb-4">
            <div class="col-12 bg-primary py-5" style="border-radius: 0 0 10px 10px">
                <h1 class="m-0">Спасибо за регистрацию на нашем сайте</h1>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <p>Данные которые вы указали при регистрации</p>
                <ul>
                    <li>Имя: {{ $data->name }}</li>
                    <li>Номер телефона: {{ $data->tel }}</li>
                    <li>Email адрес: {{ $data->email }}</li>
                </ul>
                <div class="bg-primary p-3 rounded">
                    Пароль для ввхода на сайт: <b>{{ $password }}</b>
                </div>
            </div>
        </div>
    </div>
</body>
</html>