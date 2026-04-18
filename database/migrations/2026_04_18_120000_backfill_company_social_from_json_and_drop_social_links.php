<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('companies', 'social_links')) {
            return;
        }

        $rows = DB::table('companies')
            ->select(['id', 'social_links', 'telegram', 'vk', 'inst', 'site_url'])
            ->get();

        foreach ($rows as $row) {
            if ($row->social_links === null || $row->social_links === '') {
                continue;
            }

            $raw = $row->social_links;
            $decoded = is_string($raw) ? json_decode($raw, true) : $raw;
            if (! is_array($decoded)) {
                continue;
            }

            $telegram = $row->telegram;
            $vk = $row->vk;
            $inst = $row->inst;
            $siteUrl = $row->site_url;

            foreach ($decoded as $item) {
                if (! is_array($item)) {
                    continue;
                }
                $name = isset($item['name'])
                    ? mb_strtolower(trim((string) $item['name']), 'UTF-8')
                    : '';
                $url = isset($item['url']) ? trim((string) $item['url']) : '';
                if ($url === '') {
                    continue;
                }

                if ($this->isTelegramName($name) && $this->isBlank($telegram)) {
                    $telegram = $url;
                } elseif ($this->isVkName($name) && $this->isBlank($vk)) {
                    $vk = $url;
                } elseif ($this->isInstagramName($name) && $this->isBlank($inst)) {
                    $inst = $url;
                } elseif ($this->isSiteName($name) && $this->isBlank($siteUrl)) {
                    $siteUrl = $url;
                }
            }

            $updates = [];
            if ($this->isBlank($row->telegram) && ! $this->isBlank($telegram)) {
                $updates['telegram'] = $telegram;
            }
            if ($this->isBlank($row->vk) && ! $this->isBlank($vk)) {
                $updates['vk'] = $vk;
            }
            if ($this->isBlank($row->inst) && ! $this->isBlank($inst)) {
                $updates['inst'] = $inst;
            }
            if ($this->isBlank($row->site_url) && ! $this->isBlank($siteUrl)) {
                $updates['site_url'] = $siteUrl;
            }

            if ($updates !== []) {
                DB::table('companies')->where('id', $row->id)->update($updates);
            }
        }

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('social_links');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->json('social_links')->nullable()->after('site_url');
        });
    }

    private function isBlank(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }

        return trim((string) $value) === '';
    }

    private function isTelegramName(string $name): bool
    {
        if ($name === 'tg') {
            return true;
        }

        return str_contains($name, 'telegram')
            || str_contains($name, 'телеграм');
    }

    private function isVkName(string $name): bool
    {
        return $name === 'vk'
            || str_contains($name, 'вконтакте')
            || str_contains($name, 'vkontakte');
    }

    private function isInstagramName(string $name): bool
    {
        return str_contains($name, 'instagram')
            || str_contains($name, 'инстаграм')
            || $name === 'inst';
    }

    private function isSiteName(string $name): bool
    {
        return str_contains($name, 'сайт')
            || $name === 'site'
            || str_contains($name, 'website');
    }
};
