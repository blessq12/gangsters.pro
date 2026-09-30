<?php

namespace App\Filament\Content\Company\Resources\CompanyDocumentResource\Schemas;

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
                        TextInput::make('name')
                            ->label('Название')
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
