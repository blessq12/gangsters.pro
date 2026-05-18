<?php

namespace Tests\Unit\Support\Product;

use App\Support\Product\TagColorLabels;
use Tests\TestCase;

final class TagColorLabelsTest extends TestCase
{
    public function test_options_include_primary_colors(): void
    {
        $options = TagColorLabels::options();

        $this->assertArrayHasKey('amber', $options);
        $this->assertArrayHasKey('red', $options);
        $this->assertSame('Янтарный', $options['amber']);
    }
}
