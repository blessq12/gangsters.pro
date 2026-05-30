<?php

namespace Tests\Unit\Filament\Catalog;

use App\Filament\Catalog\Support\FilamentCategoryFormMapper;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class FilamentCategoryFormMapperTest extends TestCase
{
    #[Test]
    public function to_form_state_maps_category_detail(): void
    {
        $state = FilamentCategoryFormMapper::toFormState([
            'category' => [
                'id' => 1,
                'name' => 'Роллы',
                'slug' => 'rolls',
                'sort_order' => 10,
                'is_active' => false,
            ],
            'product_links' => [],
        ]);

        $this->assertSame('Роллы', $state['name']);
        $this->assertSame(10, $state['sort_order']);
        $this->assertFalse($state['is_active']);
        $this->assertSame('rolls', $state['slug']);
    }
}
