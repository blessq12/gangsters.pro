<?php

namespace App\Infrastructure\Client\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $client_id
 * @property string $street
 * @property string $house
 * @property string $apartment
 * @property string $entrance
 * @property string $floor
 * @property string $comment
 * @property float $lat
 * @property float $lng
 * @property bool $is_default
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class ClientAddressModel extends Model
{
    protected $table = 'CLI_client_addresses';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'client_id',
        'street',
        'house',
        'apartment',
        'entrance',
        'floor',
        'comment',
        'lat',
        'lng',
        'is_default',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'client_id' => 'integer',
        'lat' => 'float',
        'lng' => 'float',
        'is_default' => 'boolean',
    ];
}
