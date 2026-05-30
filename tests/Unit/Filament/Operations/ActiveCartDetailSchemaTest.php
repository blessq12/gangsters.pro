<?php

namespace Tests\Unit\Filament\Operations;

use App\Filament\Operations\Schemas\ActiveCartDetailSchema;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use PHPUnit\Framework\TestCase;

final class ActiveCartDetailSchemaTest extends TestCase
{
    public function test_components_use_form_fields_not_infolist_entries(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4).'/app/Filament/Operations/Schemas/ActiveCartDetailSchema.php',
        );

        $this->assertStringNotContainsString('Filament\\Infolists\\', $source);
        $this->assertStringContainsString('Filament\\Forms\\Components\\', $source);
        $this->assertStringNotContainsString('TextEntry::', $source);
        $this->assertStringNotContainsString('RepeatableEntry::', $source);
        $this->assertStringNotContainsString('KeyValueEntry::', $source);
    }

    public function test_components_returns_form_based_sections(): void
    {
        $components = ActiveCartDetailSchema::components();

        $this->assertNotEmpty($components);
        $this->assertContainsOnlyInstancesOf(Section::class, $components);

        $fields = [];
        foreach ($components as $section) {
            foreach ($section->getDefaultChildComponents() as $field) {
                $fields[] = $field;
            }
        }

        $this->assertNotEmpty($fields);
        $this->assertTrue(collect($fields)->contains(fn ($field): bool => $field instanceof TextInput));
        $this->assertTrue(collect($fields)->contains(fn ($field): bool => $field instanceof Repeater));
        $this->assertTrue(collect($fields)->contains(fn ($field): bool => $field instanceof KeyValue));
    }
}
