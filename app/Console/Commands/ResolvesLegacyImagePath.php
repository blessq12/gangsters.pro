<?php

namespace App\Console\Commands;

trait ResolvesLegacyImagePath
{
    private const UPLOADS_BASE = 'uploads';

    /**
     * Ищем файл в storage/app/public/uploads и в public/uploads (legacy часто грузил в public).
     */
    private function resolveImagePath(string $path, string $uploadsPath): ?string
    {
        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, "/");

        if ($path === '') {
            return null;
        }

        if (str_starts_with($path, 'uploads/')) {
            $path = substr($path, strlen('uploads/'));
        }

        $pathNorm = ltrim($path, '/');
        $bases = [
            $uploadsPath,
            dirname($uploadsPath),
            public_path('uploads'),
            public_path(''),
        ];
        $bases = array_unique(array_filter($bases, fn ($b) => $b !== ''));

        foreach ($bases as $base) {
            $candidates = [
                $base . '/' . $pathNorm,
                $base . '/' . $path,
            ];
            foreach ($candidates as $full) {
                $full = str_replace('//', '/', $full);
                if (is_file($full)) {
                    return $full;
                }
            }
        }

        $info = pathinfo($path);
        $dir = $info['dirname'] ?? '';
        $filename = $info['filename'] ?? '';
        $ext = $info['extension'] ?? 'jpg';
        if ($dir === '.') {
            $dir = '';
        }
        $suffixes = ['-lg', '-large', '-md', '-medium', '-sm', '-small', ''];

        foreach ($bases as $base) {
            foreach ($suffixes as $suffix) {
                $try = $dir !== ''
                    ? $base . '/' . $dir . '/' . $filename . $suffix . '.' . $ext
                    : $base . '/' . $filename . $suffix . '.' . $ext;
                $try = str_replace('//', '/', $try);
                if (is_file($try)) {
                    return $try;
                }
            }
        }

        return null;
    }
}
