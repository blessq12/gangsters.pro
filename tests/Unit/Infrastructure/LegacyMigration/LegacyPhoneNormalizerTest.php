<?php

namespace Tests\Unit\Infrastructure\LegacyMigration;

use App\Infrastructure\LegacyMigration\LegacyPhoneNormalizer;
use PHPUnit\Framework\TestCase;

final class LegacyPhoneNormalizerTest extends TestCase
{
    public function test_normalizes_russian_mobile(): void
    {
        $normalizer = new LegacyPhoneNormalizer;

        $this->assertSame(
            '+7 (983) 340-90-40',
            $normalizer->normalize('+7 (983) 340-90-40'),
        );
        $this->assertSame(
            '+7 (983) 340-90-40',
            $normalizer->normalize('89833409040'),
        );
    }

    public function test_returns_null_for_empty(): void
    {
        $normalizer = new LegacyPhoneNormalizer;

        $this->assertNull($normalizer->normalize(null));
        $this->assertNull($normalizer->normalize('   '));
    }
}
