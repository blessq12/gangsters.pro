<?php

namespace App\Infrastructure\Reporting\Model;

use Illuminate\Database\Eloquent\Model;

final class ReportingClientProfile extends Model
{
    protected $table = 'reporting_client_profiles';

    public $incrementing = false;

    protected $primaryKey = 'client_id';

    protected $keyType = 'int';

    protected $fillable = [
        'client_id',
        'addresses_count',
    ];

    protected $casts = [
        'client_id' => 'int',
        'addresses_count' => 'int',
    ];
}
