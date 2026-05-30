<?php

namespace Tests\Unit\Support;

use App\Support\MarketingUploadLimits;
use App\Support\PhpIniSize;
use Tests\TestCase;

final class MarketingUploadLimitsTest extends TestCase
{
    public function test_effective_max_kb_uses_php_only_when_config_is_zero(): void
    {
        $phpKb = max(1, (int) floor(PhpIniSize::toBytes(ini_get('upload_max_filesize') ?: '2M') / 1024));

        $this->assertSame(0, (int) config('marketing.banner.max_upload_kb'));
        $this->assertSame($phpKb, MarketingUploadLimits::effectiveMaxKb('banner'));
    }

    public function test_livewire_rules_are_file_only_when_app_cap_disabled(): void
    {
        $this->assertSame(['file'], MarketingUploadLimits::livewireTemporaryUploadRules());
    }

    public function test_should_not_apply_filament_max_size_when_config_is_zero(): void
    {
        $this->assertFalse(MarketingUploadLimits::shouldApplyFilamentMaxSize('banner'));
        $this->assertFalse(MarketingUploadLimits::shouldApplyFilamentMaxSize('promotion'));
    }

    public function test_max_livewire_upload_kb_is_max_of_banner_and_promotion_effective(): void
    {
        $expected = max(
            MarketingUploadLimits::effectiveMaxKb('banner'),
            MarketingUploadLimits::effectiveMaxKb('promotion'),
        );

        $this->assertSame($expected, MarketingUploadLimits::maxLivewireUploadKb());
    }

    public function test_helper_text_mentions_php_when_app_cap_disabled(): void
    {
        $text = MarketingUploadLimits::helperText('banner');

        $this->assertStringContainsString('upload_max_filesize', $text);
        $this->assertStringNotContainsString('лимит приложения', $text);
    }
}
