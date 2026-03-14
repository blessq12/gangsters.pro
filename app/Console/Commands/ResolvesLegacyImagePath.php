<?php

namespace App\Console\Commands;

trait ResolvesLegacyImagePath
{
    private const UPLOADS_BASE = 'uploads';

    private function resolveImagePath(string $path, string $uploadsPath): ?string
    {
        $path = ltrim($path, "/\\");

        if ($path === '') {
            return null;
        }

        if (str_starts_with($path, 'uploads/')) {
            $path = substr($path, strlen('uploads/'));
        }

        $publicBase = dirname($uploadsPath);

        $candidates = [
            $uploadsPath . '/' . $path,
            $uploadsPath . '/' . ltrim($path, '/'),
            $publicBase . '/' . $path,
            $publicBase . '/' . ltrim($path, '/'),
        ];
        foreach ($candidates as $full) {
            if (is_file($full)) {
                return $full;
            }
        }
        $info = pathinfo($path);
        $dir = $info['dirname'] ?? '';
        $filename = $info['filename'] ?? '';
        $ext = $info['extension'] ?? 'jpg';
        if ($filename !== '') {
            foreach (['-lg', '-large', '-md', '-medium', '-sm', '-small', ''] as $suffix) {
                $try = $dir ? $uploadsPath . '/' . $dir . '/' . $filename . $suffix . '.' . $ext : $uploadsPath . '/' . $filename . $suffix . '.' . $ext;
                $try = str_replace('//', '/', $try);
                if (is_file($try)) {
                    return $try;
                }
            }
        }
        return null;
    }
}
