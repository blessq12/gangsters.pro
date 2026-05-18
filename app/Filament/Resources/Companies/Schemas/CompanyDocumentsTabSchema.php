<?php

namespace App\Filament\Resources\Companies\Schemas;

use App\Support\SystemContent\DocumentKeyLabels;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;

final class CompanyDocumentsTabSchema
{
    /**
     * @return list<Section>
     */
    public static function sections(): array
    {
        $sections = [];

        foreach (DocumentKeyLabels::keys() as $key) {
            $sections[] = Section::make(DocumentKeyLabels::label($key))
                ->description("Ключ: {$key}")
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make("documents.{$key}.title")
                        ->label('Название')
                        ->required()
                        ->columnSpanFull(),
                    Toggle::make("documents.{$key}.is_active")
                        ->label('Активен')
                        ->default(true)
                        ->inline(false),
                    RichEditor::make("documents.{$key}.content")
                        ->label('Текст')
                        ->columnSpanFull(),
                ]);
        }

        return $sections;
    }
}
