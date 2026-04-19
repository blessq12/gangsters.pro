<?php

namespace App\Infrastructure\Shopping\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SHP_ShoppingSession extends Model
{
    protected $table = 'SHP_shopping_sessions';

    protected $fillable = [
        'public_id',
        'client_id',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'immutable_datetime',
    ];

    public function cartLines(): HasMany
    {
        return $this->hasMany(SHP_ShoppingCartLine::class, 'shopping_session_id');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(SHP_ShoppingFavorite::class, 'shopping_session_id');
    }

    public function checkoutDraft(): HasOne
    {
        return $this->hasOne(SHP_ShoppingCheckoutDraft::class, 'shopping_session_id');
    }
}
