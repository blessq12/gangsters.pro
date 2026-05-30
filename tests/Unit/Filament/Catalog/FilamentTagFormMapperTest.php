<?php

namespace Tests\Unit\Filament\Catalog;

use App\Application\Catalog\DTO\AdminTagDTO;
use App\Filament\Catalog\Support\FilamentTagFormMapper;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class FilamentTagFormMapperTest extends TestCase
{
    #[Test]
    public function to_form_state_maps_admin_tag_dto(): void
    {
        $state = FilamentTagFormMapper::toFormState(new AdminTagDTO(
            id: 5,
            code: 'spicy',
            label: 'Острое',
            color: 'red',
            isActive: true,
            sortOrder: 3,
        ));

        $this->assertSame('Острое', $state['label']);
        $this->assertSame('red', $state['color']);
        $this->assertTrue($state['is_active']);
        $this->assertSame(3, $state['sort_order']);
        $this->assertSame('spicy', $state['code']);
    }

    #[Test]
    public function to_form_state_maps_array_payload(): void
    {
        $state = FilamentTagFormMapper::toFormState([
            'label' => 'Веган',
            'color' => 'green',
            'is_active' => false,
            'sort_order' => 1,
            'code' => 'vegan',
        ]);

        $this->assertSame('Веган', $state['label']);
        $this->assertSame('green', $state['color']);
        $this->assertFalse($state['is_active']);
        $this->assertSame('vegan', $state['code']);
    }
}
