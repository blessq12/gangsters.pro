<?php

namespace Tests\Unit\Config;

use App\Support\MarketingUploadLimits;
use Tests\TestCase;

final class MarketingConfigTest extends TestCase
{
    public function test_banner_max_upload_kb_defaults_to_zero(): void
    {
        $this->assertSame(0, (int) config('marketing.banner.max_upload_kb'));
    }

    public function test_promotion_max_upload_kb_defaults_to_zero(): void
    {
        $this->assertSame(0, (int) config('marketing.promotion.max_upload_kb'));
    }

    public function test_livewire_temporary_upload_rules_without_app_cap(): void
    {
        $rules = config('livewire.temporary_file_upload.rules');

        $this->assertIsArray($rules);
        $this->assertSame(MarketingUploadLimits::livewireTemporaryUploadRules(), $rules);
        $this->assertContains('file', $rules);
        $this->assertNotContains('max:0', $rules);

        foreach ($rules as $rule) {
            $this->assertFalse(
                is_string($rule) && str_starts_with($rule, 'max:'),
                'При отключённом лимите приложения не должно быть max: в Livewire rules',
            );
        }
    }
}
