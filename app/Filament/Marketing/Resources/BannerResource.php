<?php

namespace App\Filament\Marketing\Resources;

use App\Filament\Marketing\Resources\BannerResource\Pages\CreateBanner;
use App\Filament\Marketing\Resources\BannerResource\Pages\EditBanner;
use App\Filament\Marketing\Support\RedirectsMarketingIndexToHub;
use App\Infrastructure\SystemContent\Model\SYS_Banner;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BannerResource extends Resource
{
    use RedirectsMarketingIndexToHub;

    protected static string $marketingHubTab = 'banners';

    protected static ?string $model = SYS_Banner::class;

    protected static ?string $slug = 'marketing/banners';

    protected static ?string $modelLabel = 'баннер';

    protected static ?string $pluralModelLabel = 'баннеры';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->label('Заголовок')->maxLength(255),
            Textarea::make('description')->label('Описание'),
            TextInput::make('image')->label('Изображение (путь)'),
            TextInput::make('image_mobile')->label('Mobile (путь)'),
            TextInput::make('image_desktop')->label('Desktop (путь)'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table;
    }

    public static function getPages(): array
    {
        return [
            'create' => CreateBanner::route('/create'),
            'edit' => EditBanner::route('/{record}/edit'),
        ];
    }
}
