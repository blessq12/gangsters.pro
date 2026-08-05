<?php

namespace Tests\Feature\Crm;

use Tests\ApiTestCase;

final class КлиентApiTest extends ApiTestCase
{
    public function test_регистрация_создаёт_клиента_и_токен(): void
    {
        $телефон = $this->уникальныйТелефон();

        $ответ = $this->postJson('/api/client/register', [
            'name' => 'Анна Тест',
            'phone' => $телефон,
            'password' => 'secret12',
            'consent_personal_data' => true,
        ]);

        $ответ->assertCreated()
            ->assertJsonStructure([
                'token',
                'client' => ['id', 'name', 'phone'],
            ]);

        $this->assertNotEmpty($ответ->json('token'));
        $this->assertSame('Анна Тест', $ответ->json('client.name'));
    }

    public function test_логин_по_телефону_возвращает_токен(): void
    {
        $аккаунт = $this->зарегистрироватьКлиента();

        $ответ = $this->postJson('/api/client/login', [
            'phone' => $аккаунт['phone'],
            'password' => $аккаунт['password'],
        ]);

        $ответ->assertOk()
            ->assertJsonStructure(['token', 'client' => ['id']])
            ->assertJsonPath('client.id', $аккаунт['client']['id']);
    }

    public function test_профиль_читается_и_обновляется(): void
    {
        $аккаунт = $this->зарегистрироватьКлиента();

        $чтение = $this->сТокеном($аккаунт['token'])
            ->getJson('/api/client/profile');

        $чтение->assertOk()
            ->assertJsonPath('client.id', $аккаунт['client']['id']);

        $обновление = $this->сТокеном($аккаунт['token'])
            ->patchJson('/api/client/profile', [
                'name' => 'Новое Имя',
                'consent_marketing' => true,
            ]);

        $обновление->assertOk()
            ->assertJsonPath('client.name', 'Новое Имя')
            ->assertJsonPath('client.consent_marketing', true);
    }

    public function test_адрес_добавляется_и_удаляется(): void
    {
        $аккаунт = $this->зарегистрироватьКлиента();

        $добавление = $this->сТокеном($аккаунт['token'])
            ->postJson('/api/client/addresses', [
                'street' => 'Ленина',
                'house' => '10',
                'apartment' => '5',
                'make_default' => true,
            ]);

        $добавление->assertCreated();
        $адреса = $добавление->json('client.addresses');
        $this->assertCount(1, $адреса);
        $addressId = $адреса[0]['id'];
        $this->assertTrue($адреса[0]['is_default']);

        $удаление = $this->сТокеном($аккаунт['token'])
            ->deleteJson('/api/client/addresses/'.$addressId);

        $удаление->assertOk();
        $this->assertSame([], $удаление->json('client.addresses'));
    }

    public function test_избранное_toggle_remove_и_merge(): void
    {
        $аккаунт = $this->зарегистрироватьКлиента();
        $productId = $this->idАктивногоТовара();
        $другойProductId = $productId + 1;

        $toggle = $this->сТокеном($аккаунт['token'])
            ->postJson('/api/client/favorites/'.$productId, [
                'name' => 'Тестовый ролл',
                'price' => 500,
            ]);

        $toggle->assertOk();
        $this->assertCount(1, $toggle->json('favorites'));
        $this->assertSame($productId, (int) $toggle->json('favorites.0.productId'));

        $список = $this->сТокеном($аккаунт['token'])
            ->getJson('/api/client/favorites');

        $список->assertOk()
            ->assertJsonCount(1, 'favorites');

        $merge = $this->сТокеном($аккаунт['token'])
            ->postJson('/api/client/favorites/merge', [
                'items' => [
                    [
                        'product_id' => $другойProductId,
                        'product_name' => 'Гостевой товар',
                        'price_rub' => 100,
                    ],
                ],
            ]);

        $merge->assertOk();
        $this->assertGreaterThanOrEqual(2, count($merge->json('favorites')));

        $remove = $this->сТокеном($аккаунт['token'])
            ->deleteJson('/api/client/favorites/'.$productId);

        $remove->assertOk();
        $оставшиеся = collect($remove->json('favorites'))
            ->pluck('productId')
            ->map(fn ($id) => (int) $id)
            ->all();
        $this->assertNotContains($productId, $оставшиеся);
    }

    public function test_история_заказов_и_повтор_строк(): void
    {
        $аккаунт = $this->зарегистрироватьКлиента();
        $clientId = (int) $аккаунт['client']['id'];
        $productId = $this->idАктивногоТовара();

        $заказ = $this->оформитьЗаказДляКлиента(
            $аккаунт['token'],
            $clientId,
            $productId,
        );

        $история = $this->сТокеном($аккаунт['token'])
            ->getJson('/api/client/orders');

        $история->assertOk()
            ->assertJsonStructure(['data']);

        $ids = collect($история->json('data'))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
        $this->assertContains($заказ['order_id'], $ids);

        $повтор = $this->сТокеном($аккаунт['token'])
            ->getJson('/api/client/orders/'.$заказ['order_id'].'/repeatable-lines');

        $повтор->assertOk()
            ->assertJsonStructure([
                'available_lines',
                'unavailable_lines',
            ]);

        $availableIds = collect($повтор->json('available_lines'))
            ->pluck('product_id')
            ->map(fn ($id) => (int) $id)
            ->all();
        $this->assertContains($productId, $availableIds);
    }

    public function test_защищённые_эндпоинты_без_токена_дают_401(): void
    {
        $this->getJson('/api/client/profile')->assertUnauthorized();
        $this->getJson('/api/client/favorites')->assertUnauthorized();
        $this->getJson('/api/client/orders')->assertUnauthorized();
    }
}
