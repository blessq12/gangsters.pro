<?php

namespace App\Filament\Content\Company\Resources\CompanyDocumentResource\Schemas;

use App\Filament\Content\Company\Resources\CompanyDocumentResource;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class CompanyDocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Документ')
                    ->schema([
                        TextInput::make('key')
                            ->label('Ключ')
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(
                                fn (?string $state): string => CompanyDocumentResource::documentDefinitions()[$state]
                                    ?? (string) $state,
                            ),
                        TextInput::make('title')
                            ->label('Заголовок')
                            ->required()
                            ->maxLength(255),
                        RichEditor::make('content')
                            ->label('Содержимое')
                            ->columnSpanFull()
                            ->fileAttachments(false)
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'h2',
                                'h3',
                                'bulletList',
                                'orderedList',
                                'link',
                                'undo',
                                'redo',
                            ])
                            ->helperText('HTML для публичных страниц / модалок футера.'),
                    ]),
            ]);
    }
}
