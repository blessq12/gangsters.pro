<?php

namespace App\Filament\Catalog\Forms\Components;

use Filament\Forms\Components\Field;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * UI-only поле: пишет в sibling focus_x / focus_y, само не dehydrate'ится.
 */
class FocalPointPicker extends Field
{
    protected string $view = 'filament.forms.components.focal-point-picker';

    protected string $imageFieldName = 'path';

    protected string $focusXFieldName = 'focus_x';

    protected string $focusYFieldName = 'focus_y';

    protected string $imageDisk = 'public';

    protected function setUp(): void
    {
        parent::setUp();

        $this->dehydrated(false);
    }

    public function imageField(string $name): static
    {
        $this->imageFieldName = $name;

        return $this;
    }

    public function focusXField(string $name): static
    {
        $this->focusXFieldName = $name;

        return $this;
    }

    public function focusYField(string $name): static
    {
        $this->focusYFieldName = $name;

        return $this;
    }

    public function imageDisk(string $disk): static
    {
        $this->imageDisk = $disk;

        return $this;
    }

    public function getImageFieldName(): string
    {
        return $this->imageFieldName;
    }

    public function getFocusXFieldName(): string
    {
        return $this->focusXFieldName;
    }

    public function getFocusYFieldName(): string
    {
        return $this->focusYFieldName;
    }

    public function getFocusXStatePath(): string
    {
        return $this->resolveSiblingStatePath($this->focusXFieldName);
    }

    public function getFocusYStatePath(): string
    {
        return $this->resolveSiblingStatePath($this->focusYFieldName);
    }

    public function getImageStatePath(): string
    {
        return $this->resolveSiblingStatePath($this->imageFieldName);
    }

    public function resolveSiblingStatePath(string $relativePath): string
    {
        $statePath = $this->getStatePath();
        $prefix = str_contains($statePath, '.')
            ? substr($statePath, 0, (int) strrpos($statePath, '.'))
            : '';

        if ($prefix === '') {
            return $relativePath;
        }

        return "{$prefix}.{$relativePath}";
    }

    public function resolveImageUrl(mixed $path): ?string
    {
        if (is_array($path)) {
            $path = Arr::first($path);
        }

        if ($path instanceof TemporaryUploadedFile) {
            try {
                return $path->temporaryUrl();
            } catch (\Throwable) {
                return null;
            }
        }

        if (! is_string($path)) {
            return null;
        }

        $path = trim($path);

        if ($path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        return Storage::disk($this->imageDisk)->url($path);
    }
}
