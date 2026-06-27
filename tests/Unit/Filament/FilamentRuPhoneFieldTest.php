<?php

namespace Tests\Unit\Filament;

use App\Filament\Support\FilamentRuPhoneField;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class FilamentRuPhoneFieldTest extends TestCase
{
    #[Test]
    public function format_state_приводит_к_канону(): void
    {
        $this->assertSame(
            '+7 (999) 000-11-22',
            FilamentRuPhoneField::formatState('+79990001122'),
        );
    }

    #[Test]
    public function dehydrate_state_сохраняет_канон(): void
    {
        $this->assertSame(
            '+7 (999) 000-11-22',
            FilamentRuPhoneField::dehydrateState('+7 (999) 000-11-22'),
        );
        $this->assertNull(FilamentRuPhoneField::dehydrateState(''));
    }
}
