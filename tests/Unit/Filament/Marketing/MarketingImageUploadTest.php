<?php

namespace Tests\Unit\Filament\Marketing;

use App\Filament\Marketing\Support\MarketingImageUpload;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Text;
use ReflectionClass;
use Tests\TestCase;

final class MarketingImageUploadTest extends TestCase
{
    public function test_banner_mobile_helper_text_shows_aspect_ratio_hint(): void
    {
        $this->assertSame(
            'Соотношение сторон: 3/4 (вертикально). Рекоменд. размеры: 900×1200 или 1200×1600.',
            $this->helperTextFrom(MarketingImageUpload::bannerMobile()),
        );
    }

    public function test_banner_desktop_helper_text_shows_aspect_ratio_hint(): void
    {
        $this->assertSame(
            'Соотношение сторон: 4/3 (горизонтально). Рекоменд. размеры: 1200×900 или 1600×1200.',
            $this->helperTextFrom(MarketingImageUpload::bannerDesktop()),
        );
    }

    public function test_promotion_helper_text_recommends_png_with_empty_background(): void
    {
        $this->assertSame(
            'Рекомендуется использовать PNG с пустым фоном.',
            $this->helperTextFrom(MarketingImageUpload::promotion()),
        );
    }

    private function helperTextFrom(FileUpload $field): ?string
    {
        $reflection = new ReflectionClass($field);
        $property = $reflection->getProperty('childComponents');
        $property->setAccessible(true);

        /** @var array<string, mixed> $childComponents */
        $childComponents = $property->getValue($field);

        $belowContent = $childComponents[Field::BELOW_CONTENT_SCHEMA_KEY] ?? null;

        if (! is_callable($belowContent)) {
            return null;
        }

        $component = $belowContent($field);

        if (! $component instanceof Text) {
            return null;
        }

        $content = $component->getContent();

        return is_string($content) ? $content : null;
    }
}
