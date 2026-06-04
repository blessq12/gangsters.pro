<?php

namespace Tests\Unit\Filament\Catalog;

use App\Filament\Catalog\Support\FilamentTagFormMapper;
use App\Infrastructure\Product\Model\PRD_Tag;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class FilamentTagFormMapperTest extends TestCase
{
    #[Test]
    public function to_form_state_maps_tag_model(): void
    {
        $tag = new PRD_Tag([
            'code' => 'spicy',
            'label' => 'Острое',
            'color' => 'red',
            'is_active' => true,
            'sort_order' => 3,
        ]);
        $tag->id = 5;

        $state = FilamentTagFormMapper::toFormState($tag);

        $this->assertSame('Острое', $state['label']);
        $this->assertSame('red', $state['color']);
        $this->assertTrue($state['is_active']);
        $this->assertSame(3, $state['sort_order']);
        $this->assertSame('spicy', $state['code']);
    }
}
