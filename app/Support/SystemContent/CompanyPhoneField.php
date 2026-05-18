<?php

namespace App\Support\SystemContent;

use Closure;
use Filament\Forms\Components\TextInput;

final class CompanyPhoneField
{
    public static function make(string $name, string $label, bool $required = false): TextInput
    {
        $field = TextInput::make($name)
            ->label($label)
            ->mask('+7 (999) 999-99-99')
            ->formatStateUsing(fn (?string $state): ?string => self::normalize($state))
            ->dehydrateStateUsing(fn (?string $state): ?string => self::normalize($state))
            ->rule(self::rule("{$label} должен быть в формате +7 (XXX) XXX-XX-XX."));

        return $required ? $field->required() : $field;
    }

    public static function normalize(?string $state): ?string
    {
        if ($state === null || $state === '') {
            return $state;
        }

        $digits = preg_replace('/\D+/', '', $state) ?? '';
        if (preg_match('/^(7|8)\d{10}$/', $digits) === 1) {
            $digits = substr($digits, 1);
        }

        if (preg_match('/^\d{10}$/', $digits) !== 1) {
            return $state;
        }

        return sprintf(
            '+7 (%s) %s-%s-%s',
            substr($digits, 0, 3),
            substr($digits, 3, 3),
            substr($digits, 6, 2),
            substr($digits, 8, 2),
        );
    }

    private static function rule(string $message): Closure
    {
        return function () use ($message) {
            return function (string $attribute, $value, \Closure $fail) use ($message): void {
                if ($value === null || $value === '') {
                    return;
                }

                $digits = preg_replace('/\D+/', '', (string) $value) ?? '';
                if (preg_match('/^(7|8)\d{10}$/', $digits) === 1) {
                    return;
                }
                if (preg_match('/^\d{10}$/', $digits) === 1) {
                    return;
                }

                $fail($message);
            };
        };
    }
}
