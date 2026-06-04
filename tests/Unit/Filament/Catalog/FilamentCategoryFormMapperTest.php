<?php

namespace Tests\Unit\Filament\Catalog;

use App\Filament\Catalog\Support\FilamentCategoryFormMapper;
use App\Infrastructure\Category\Model\PRD_Category;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class FilamentCategoryFormMapperTest extends TestCase
{
    #[Test]
    public function to_form_state_maps_category_model(): void
    {
        $category = new PRD_Category([
            'name' => 'Роллы',
            'slug' => 'rolls',
            'sort_order' => 10,
            'is_active' => false,
        ]);

        $state = FilamentCategoryFormMapper::toFormState($category);

        $this->assertSame('Роллы', $state['name']);
        $this->assertSame(10, $state['sort_order']);
        $this->assertFalse($state['is_active']);
        $this->assertNull($state['slug']);
    }
}
