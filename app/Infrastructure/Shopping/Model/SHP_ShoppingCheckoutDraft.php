<?php

namespace App\Infrastructure\Shopping\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SHP_ShoppingCheckoutDraft extends Model
{
    protected $table = 'SHP_shopping_checkout_drafts';

    protected $fillable = [
        'shopping_session_id',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(SHP_ShoppingSession::class, 'shopping_session_id');
    }
}
