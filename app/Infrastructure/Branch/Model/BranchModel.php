<?php

namespace App\Infrastructure\Branch\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property bool $is_active
 * @property array $schedule
 * @property string $city
 * @property string $street
 * @property string $house
 * @property string $comment
 * @property float $latitude
 * @property float $longitude
 * @property string $phone
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class BranchModel extends Model
{
    protected $table = 'CMP_branches';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'name',
        'is_active',
        'schedule',
        'city',
        'street',
        'house',
        'comment',
        'latitude',
        'longitude',
        'phone',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'company_id' => 'integer',
        'is_active' => 'boolean',
        'schedule' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
    ];
}
