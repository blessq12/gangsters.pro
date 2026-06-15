<?php

namespace App\Infrastructure\AggregatorIngress\Model;

use Illuminate\Database\Eloquent\Model;

final class ING_PartnerSkuBinding extends Model
{
    protected $table = 'ING_partner_sku_bindings';

    protected $fillable = [
        'partner_code',
        'partner_sku',
        'product_id',
    ];
}
