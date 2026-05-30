<?php

namespace App\Filament\Company\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class SiteSeoSettingsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Значения по умолчанию')
                    ->description('Задаются в .env (SITE_DEFAULT_TITLE, SITE_DEFAULT_DESCRIPTION) и config/site.php. Для страниц без своей записи используются эти значения.')
                    ->schema([
                        TextInput::make('default_title')
                            ->label('Title по умолчанию')
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull(),
                        Textarea::make('default_description')
                            ->label('Description по умолчанию')
                            ->disabled()
                            ->dehydrated(false)
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                Section::make('Страницы')
                    ->schema([
                        Repeater::make('pages')
                            ->label('SEO по путям')
                            ->schema([
                                TextInput::make('path')
                                    ->label('Путь')
                                    ->placeholder('/delivery')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('title')
                                    ->label('Title')
                                    ->required()
                                    ->maxLength(255),
                                Textarea::make('description')
                                    ->label('Description')
                                    ->required()
                                    ->rows(2)
                                    ->maxLength(500),
                                Select::make('robots')
                                    ->label('Robots')
                                    ->options([
                                        'index,follow' => 'index, follow',
                                        'noindex,nofollow' => 'noindex, nofollow',
                                        'noindex,follow' => 'noindex, follow',
                                        'index,nofollow' => 'index, nofollow',
                                    ])
                                    ->required()
                                    ->native(false),
                            ])
                            ->columns(2)
                            ->itemLabel(fn (array $state): ?string => is_string($state['path'] ?? null) && $state['path'] !== ''
                                ? $state['path']
                                : null)
                            ->defaultItems(0)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
