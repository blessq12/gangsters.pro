<?php

use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\YandexFoodController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RawController as Raw;
use App\Application\Client\Command\LoginClientUseCase;
use App\Application\Client\Command\RegisterClientUseCase;
use App\Application\Client\DTO\LoginDTO;
use App\Application\Client\DTO\RegisterDTO;
use App\Application\Client\Presenter\ClientPresenter;
use App\Application\Client\Query\GetClientDataUseCase;
use App\Infrastructure\Client\Model\UR_Client;
use App\Infrastructure\Client\Repository\ClientRepository as InfraClientRepository;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Основные слои: client, order, product.
|
*/

Route::prefix('client')->controller(ClientController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
    Route::post('/forgot-password', 'forgotPassword');
    Route::post('/change-password', 'changePassword');
    Route::get('/profile', 'profile');
    Route::patch('/profile', 'updateProfile');
    Route::post('/addresses', 'addAddress');
    Route::delete('/addresses/{id}', 'deleteAddress');
});

Route::prefix('order')->controller(OrderController::class)->group(function () {
    Route::get('/my-orders', 'getMyOrders');
    Route::get('/my-coins', 'getMyCoins');
    Route::post('/', 'createOrder');
    Route::post('/update', 'updateOrder');
    Route::post('/check-availability', 'checkAvalibility');
});

Route::prefix('product')->controller(ProductController::class)->group(function () {
    Route::get('/categories', 'categories');
    Route::get('/products', 'products');
});

// Весь каталог: категории с товарами
Route::get('/catalog', [CatalogController::class, 'tree']);

Route::controller(Raw::class)->group(function () {
    Route::get('/get-routes', 'getLinks');
    Route::get('/get-company', 'getCompany');
    Route::get('/get-shedule', 'getShedule');
});

Route::controller(YandexFoodController::class)
    ->prefix('yandex-food')
    ->group(function () {
        Route::post('/security/oauth/token', 'login');
        Route::get('/menu/{id}/composition', 'getMenuComposition');
        Route::get('/menu/{id}/availability', 'getMenuAvailability');
        Route::get('/menu/{id}/promos', 'getMenuPromos');
        Route::post('/order', 'createOrder');
        Route::get('/order/{id}', 'getOrderById');
        Route::get('/order/{id}/status', 'getOrderStatus');
        Route::put('/order/{id}/', 'updateOrder');
        Route::delete('/order/{id}/', 'deleteOrder');
        Route::get('/restaurants', 'getRestaurants');
    });

// Тестовые/вторичные маршруты для нового домена клиента (регистрация, логин, профиль).
Route::prefix('test-client')->group(function () {
    Route::post('/register', function (Request $request, Hasher $hasher) {
        $repo = new InfraClientRepository();
        $useCase = new RegisterClientUseCase($repo, $hasher);
        $presenter = new ClientPresenter();

        $dto = new RegisterDTO(
            name: $request->input('name'),
            phone: $request->input('phone'),
            email: $request->input('email'),
            birthDate: $request->input('birth_date'),
            password: $request->input('password'),
            consentPersonalData: (bool) $request->boolean('consent_personal_data'),
            consentMarketing: (bool) $request->boolean('consent_marketing'),
        );

        $client = $useCase->execute($dto);

        $clientModel = UR_Client::findOrFail($client->id());
        $token = $clientModel->createToken('client')->plainTextToken;

        return response()->json([
            'client' => $presenter->present($client),
            'token' => $token,
        ]);
    });

    Route::post('/login', function (Request $request, Hasher $hasher) {
        $repo = new InfraClientRepository();
        $useCase = new LoginClientUseCase($repo, $hasher);
        $presenter = new ClientPresenter();

        $dto = new LoginDTO(
            phone: $request->input('phone'),
            email: $request->input('email'),
            password: $request->input('password'),
        );

        $client = $useCase->execute($dto);

        $clientModel = UR_Client::findOrFail($client->id());
        $token = $clientModel->createToken('client')->plainTextToken;

        return response()->json([
            'client' => $presenter->present($client),
            'token' => $token,
        ]);
    });

    Route::middleware('auth:sanctum')->get('/me', function (Request $request, Hasher $hasher) {
        $repo = new InfraClientRepository();
        $useCase = new GetClientDataUseCase($repo, $hasher);
        $presenter = new ClientPresenter();

        /** @var UR_Client $authClient */
        $authClient = $request->user();
        $client = $useCase->execute($authClient->id);

        return response()->json([
            'client' => $presenter->present($client),
        ]);
    });
});
