<?php

namespace App\Infrastructure\Integration\Frontpad;

use Illuminate\Support\Facades\Log;

/**
 * Единая точка записи в канал frontpad (отдельный файл).
 */
final class FrontpadLogger
{
    private const CHANNEL = 'frontpad';

    /**
     * @param  array<string, mixed>  $context
     */
    public static function debug(string $message, array $context = []): void
    {
        Log::channel(self::CHANNEL)->debug($message, self::normalize($context));
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public static function info(string $message, array $context = []): void
    {
        Log::channel(self::CHANNEL)->info($message, self::normalize($context));
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public static function warning(string $message, array $context = []): void
    {
        Log::channel(self::CHANNEL)->warning($message, self::normalize($context));
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public static function error(string $message, array $context = []): void
    {
        Log::channel(self::CHANNEL)->error($message, self::normalize($context));
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public static function sanitizePayload(array $payload): array
    {
        $sanitized = $payload;

        if (array_key_exists('secret', $sanitized)) {
            $sanitized['secret'] = '***';
        }

        if (isset($sanitized['hook_url']) && is_string($sanitized['hook_url'])) {
            $sanitized['hook_url'] = self::maskSecretInUrl($sanitized['hook_url']);
        }

        return $sanitized;
    }

    private static function maskSecretInUrl(string $url): string
    {
        if (! str_contains($url, 'secret=')) {
            return $url;
        }

        return (string) preg_replace('/([?&]secret=)[^&]*/', '$1***', $url);
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private static function normalize(array $context): array
    {
        if (isset($context['payload']) && is_array($context['payload'])) {
            $context['payload'] = self::sanitizePayload($context['payload']);
        }

        if (isset($context['body']) && is_array($context['body'])) {
            $context['body'] = self::sanitizePayload($context['body']);
        }

        return $context;
    }
}
