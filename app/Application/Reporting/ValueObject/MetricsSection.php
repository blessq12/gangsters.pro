<?php

namespace App\Application\Reporting\ValueObject;

enum MetricsSection: string
{
    case Overview = 'overview';
    case Finance = 'finance';
    case Clients = 'clients';
    case Orders = 'orders';
    case Storefront = 'storefront';
}
