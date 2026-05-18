<?php

namespace App\Filament\Resources\CompanyLegals\Schemas;

use App\Filament\Resources\Companies\Schemas\CompanyLegalTabSchema;
use Filament\Schemas\Schema;

class CompanyLegalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components(CompanyLegalTabSchema::sections(''));
    }
}
