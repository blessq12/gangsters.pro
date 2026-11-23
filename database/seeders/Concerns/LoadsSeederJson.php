<?php

namespace Database\Seeders\Concerns;

use RuntimeException;

trait LoadsSeederJson
{
    /**
     * @return array<string, mixed>
     */
    protected function loadSeederJson(string $relativeName): array
    {
        $path = database_path('seeders/data/'.$relativeName);
        if (! is_file($path)) {
            throw new RuntimeException('Нет файла сидера: '.$path);
        }

        $decoded = json_decode((string) file_get_contents($path), true);
        if (! is_array($decoded)) {
            throw new RuntimeException('Битый JSON сидера: '.$path);
        }

        return $decoded;
    }
}
