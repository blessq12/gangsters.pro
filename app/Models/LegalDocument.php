<?php

namespace App\Models;

use App\Enums\LegalDocumentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LegalDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'version',
        'title',
        'body_html',
        'is_current',
        'published_at',
    ];

    protected $casts = [
        'is_current' => 'boolean',
        'published_at' => 'datetime',
        'version' => 'integer',
    ];

    public function consents(): HasMany
    {
        return $this->hasMany(LegalConsent::class);
    }

    public function typeLabel(): string
    {
        return LegalDocumentType::LABELS[$this->type] ?? $this->type;
    }

    public function publicUrl(): ?string
    {
        $route = LegalDocumentType::ROUTES[$this->type] ?? null;

        return $route ? route($route) : null;
    }
}
