<?php

namespace App\Infrastructure\Payment\Provider;

use App\Domain\Payment\Repository\PaymentRepository;
use App\Infrastructure\Payment\Repository\EloquentPaymentRepository;
use Illuminate\Support\ServiceProvider;

final class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PaymentRepository::class, EloquentPaymentRepository::class);
    }
}
