<?php

namespace App\Console\Commands;

use App\Support\MarketingUploadLimits;
use App\Support\PhpIniSize;
use Illuminate\Console\Command;

final class CheckMarketingUploadLimitsCommand extends Command
{
    private const RECOMMENDED_PHP_UPLOAD_BYTES = 64 * 1024 * 1024;

    protected $signature = 'marketing:check-upload-limits';

    protected $description = 'Проверить лимиты PHP и config/marketing для загрузки баннеров и акций';

    public function handle(): int
    {
        $uploadMax = ini_get('upload_max_filesize') ?: 'unknown';
        $postMax = ini_get('post_max_size') ?: 'unknown';
        $bannerConfigKb = (int) config('marketing.banner.max_upload_kb');
        $promotionConfigKb = (int) config('marketing.promotion.max_upload_kb');
        $bannerEffectiveKb = MarketingUploadLimits::effectiveMaxKb('banner');
        $promotionEffectiveKb = MarketingUploadLimits::effectiveMaxKb('promotion');
        $livewireRules = implode(', ', MarketingUploadLimits::livewireTemporaryUploadRules());

        $this->table(
            ['Параметр', 'Значение'],
            [
                ['PHP upload_max_filesize', $uploadMax.' ('.PhpIniSize::toMegabytesLabel($uploadMax).')'],
                ['PHP post_max_size', $postMax.' ('.PhpIniSize::toMegabytesLabel($postMax).')'],
                ['config marketing.banner.max_upload_kb', $bannerConfigKb === 0 ? '0 (только PHP)' : (string) $bannerConfigKb],
                ['effective banner max (KB)', (string) $bannerEffectiveKb],
                ['config marketing.promotion.max_upload_kb', $promotionConfigKb === 0 ? '0 (только PHP)' : (string) $promotionConfigKb],
                ['effective promotion max (KB)', (string) $promotionEffectiveKb],
                ['Livewire temporary_file_upload rules', $livewireRules],
            ],
        );

        $uploadBytes = PhpIniSize::toBytes($uploadMax);

        if ($bannerConfigKb > 0 && $uploadBytes < $bannerConfigKb * 1024) {
            $this->warn('PHP upload_max_filesize меньше лимита приложения (banner) — загрузки будут падать.');
            $this->line('См. docs/admin-media-upload-limits.md');

            return self::FAILURE;
        }

        if ($bannerConfigKb <= 0 && $promotionConfigKb <= 0) {
            $this->info('Лимит приложения отключён — действует только PHP/nginx.');

            if ($uploadBytes < self::RECOMMENDED_PHP_UPLOAD_BYTES) {
                $this->warn('Рекомендуется upload_max_filesize не ниже 64M для крупных баннеров.');

                return self::FAILURE;
            }

            return self::SUCCESS;
        }

        $this->info('Лимиты PHP достаточны для настроек приложения.');

        return self::SUCCESS;
    }
}
