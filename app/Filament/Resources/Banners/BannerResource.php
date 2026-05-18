<?php

namespace App\Filament\Resources\Banners;

use App\Filament\Resources\Banners\Pages\CreateBanner;
use App\Filament\Resources\Banners\Pages\EditBanner;
use App\Filament\Resources\Banners\Pages\ListBanners;
use App\Filament\Resources\Banners\Tables\BannersTable;
use App\Infrastructure\SystemContent\Model\SYS_Banner;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class BannerResource extends Resource
{
    protected static ?string $model = SYS_Banner::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $navigationLabel = 'Баннеры';

    protected static string|UnitEnum|null $navigationGroup = 'Маркетинг';

    protected static ?int $navigationSort = 40;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('image_mobile')
                    ->label('Картинка (мобилка)')
                    ->image()
                    ->disk('media')
                    ->directory('banners')
                    ->required()
                    ->helperText('Соотношение сторон: 3/4 (вертикально). Рекоменд. размеры: 900×1200 или 1200×1600.')
                    ->columnSpanFull(),
                FileUpload::make('image_desktop')
                    ->label('Картинка (десктоп)')
                    ->image()
                    ->disk('media')
                    ->directory('banners')
                    ->required()
                    ->helperText('Соотношение сторон: 4/3 (горизонтально). Рекоменд. размеры: 1200×900 или 1600×1200.')
                    ->columnSpanFull(),
                TextInput::make('title')
                    ->label('Заголовок')
                    ->maxLength(255)
                    ->columnSpanFull(),
                TextInput::make('description')
                    ->label('Описание')
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return BannersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBanners::route('/'),
            'create' => CreateBanner::route('/create'),
            'edit' => EditBanner::route('/{record}/edit'),
        ];
    }
}
