<?php

namespace App\Providers;

use App\Domain\Order\Entities\Order;
use App\Domain\Order\Repositories\OrderRepositoryInterface as OrderRepositoryContract;
use App\Domain\Order\Services\OrderIdGenerator;
use App\Domain\Order\Integrations\FrontpadOrderGateway;
use App\Infrastructure\Order\Repository\OrderRepository as EloquentOrderRepository;
use App\Infrastructure\Order\Service\RandomOrderIdGenerator;
use App\Infrastructure\Order\Integrations\FrontpadOrderGatewayImpl;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class OrderServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(OrderRepositoryContract::class, EloquentOrderRepository::class);
        $this->app->bind(OrderIdGenerator::class, RandomOrderIdGenerator::class);
        $this->app->bind(FrontpadOrderGateway::class, function () {
            $enabled = (bool) config('services.frontpad.enabled', false);
            $apiUrl = (string) config('services.frontpad.api_url', '');
            $apiSecret = (string) config('services.frontpad.api_secret', '');
            $hookUrl = (string) config('services.frontpad.hook_url', '');
            $degradationMode = (string) config('services.frontpad.degradation_mode', 'fail_open');
            $failSilently = $degradationMode !== 'fail_closed';

            if (! $enabled || $apiUrl === '' || $apiSecret === '') {
                return new class implements FrontpadOrderGateway
                {
                    public function pushOrder(Order $order): void
                    {
                        // Frontpad интеграция выключена, сохраняем no-op поведение.
                    }
                };
            }

            return new FrontpadOrderGatewayImpl(
                new Client([
                    'timeout' => 5,
                ]),
                $apiUrl,
                $apiSecret,
                $hookUrl,
                $failSilently,
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
    }
}
