<?php

namespace Tests\Unit\YandexFood;

use App\Domain\YandexFood\Exception\YandexFoodOAuthRejectedException;
use App\Infrastructure\YandexFood\Auth\ConfigYandexFoodAuthenticator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ConfigYandexFoodAuthenticatorTest extends TestCase
{
    #[Test]
    public function bearer_принимает_валидный_токен(): void
    {
        config([
            'yandex_food.enabled' => true,
            'yandex_food.auth_token' => 'phpunit-yandex-bearer-token',
        ]);

        $authenticator = new ConfigYandexFoodAuthenticator();

        $authenticator->authenticateBearer('phpunit-yandex-bearer-token');

        $this->assertTrue(true);
    }

    #[Test]
    public function oauth_отклоняет_неверные_секреты(): void
    {
        config([
            'yandex_food.enabled' => true,
            'yandex_food.client_id' => 'phpunit-yandex-client-id',
            'yandex_food.client_secret' => 'secret',
        ]);

        $authenticator = new ConfigYandexFoodAuthenticator();

        $this->expectException(YandexFoodOAuthRejectedException::class);

        $authenticator->authenticateClient('phpunit-yandex-client-id', 'wrong');
    }
}
