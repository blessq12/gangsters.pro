<?php

namespace App\Infrastructure\Company\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CMP_CompanyLegal extends Model
{
    protected $table = 'CMP_company_legal';

    protected $fillable = [
        'company_id',
        'full_name',
        'short_name',
        'legal_form',
        'legal_email',
        'contracts_email',
        'legal_phone',
        'owner',
        'responsible_person',
        'responsible_position',
        'inn',
        'ogrn',
        'ogrnip',
        'okpo',
        'kpp',
        'tax_system',
        'is_vat_payer',
        'vat_rate_default',
        'registration_address',
        'actual_address',
        'postal_address',
        'bank_name',
        'bik',
        'checking_account',
        'correspondent_account',
    ];

    protected $casts = [
        'is_vat_payer' => 'boolean',
        'vat_rate_default' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CMP_Company::class, 'company_id');
    }
}
