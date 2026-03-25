<?php

namespace App\Filament\Resources\Banners;

use App\Filament\Resources\Banners\Pages\CreateBanner;
use App\Filament\Resources\Banners\Pages\EditBanner;
use App\Filament\Resources\Banners\Pages\ListBanners;
use App\Infrastructure\SystemContent\Model\SYS_Banner;
use BackedEnum;
use UnitEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;

class BannerResource extends Resource
{
    protected static ?string $model = SYS_Banner::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $navigationLabel = 'Баннеры';

    // Группа: блок контента и промо
    protected static string|UnitEnum|null $navigationGroup = 'Контент и промо';

    // Сортировка в навигации внутри блока контента
    protected static ?int $navigationSort = 40;

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
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_mobile')
                    ->label('Мобилка')
                    ->disk('media')
                    ->square(),
                Tables\Columns\ImageColumn::make('image_desktop')
                    ->label('Десктоп')
                    ->disk('media')
                    ->square(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Заголовок')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Описание')
                    ->limit(50),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make()->iconButton(),
            ], position: RecordActionsPosition::BeforeCells)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
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

