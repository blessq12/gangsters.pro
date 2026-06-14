<?php

namespace App\Filament\MarketingContent\Concerns;

trait PreservesMarketingMediaOnEmptyUpload
{
    /**
     * Filament FileUpload не показывает файлы вне public disk (например /images/* из сидера).
     * При сохранении без новой загрузки не затираем существующий путь.
     *
     * @param  list<string>  $fields
     */
    protected function preserveMarketingMediaPaths(array $data, array $fields): array
    {
        $record = $this->getRecord();

        foreach ($fields as $field) {
            if (blank($data[$field] ?? null) && filled($record->{$field})) {
                $data[$field] = $record->{$field};
            }
        }

        return $data;
    }
}
