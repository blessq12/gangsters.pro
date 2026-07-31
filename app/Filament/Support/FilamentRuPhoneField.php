<?php

namespace App\Filament\Support;

use App\Domain\Client\ValueObject\PhoneNumber;
use Closure;
use Filament\Forms\Components\TextInput;

/**
 * Russian phone field for Filament: mask +7 (XXX) XXX-XX-XX (Alpine mask).
 */
final class FilamentRuPhoneField
{
    public const MASK = '+7 (999) 999-99-99';

    public const PLACEHOLDER = '+7 (912) 345-67-89';

    public const TEL_REGEX = '/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/';

    public static function make(string $name, ?string $label = null, bool $required = false): TextInput
    {
        $field = TextInput::make($name)
            ->label($label ?? 'Телефон')
            ->tel()
            ->telRegex(self::TEL_REGEX)
            ->mask(self::MASK)
            ->placeholder(self::PLACEHOLDER)
            ->maxLength(20)
            ->formatStateUsing(self::formatState(...))
            ->dehydrateStateUsing(self::dehydrateState(...))
            ->rules(self::validationRules($required));

        if ($required) {
            $field->required();
        }

        return $field;
    }

    public static function makeReadOnly(string $name, ?string $label = null): TextInput
    {
        return TextInput::make($name)
            ->label($label ?? 'Телефон')
            ->formatStateUsing(self::formatState(...))
            ->disabled()
            ->dehydrated(false);
    }

    /**
     * @return list<string|Closure>
     */
    public static function validationRules(bool $required = false): array
    {
        // Filament v4: outer Closure is resolved via DI and must return a Laravel rule;
        // otherwise [$attribute] is treated as a field utility and fails as unresolvable.
        $rules = [
            'string',
            'max:20',
            static fn (): Closure => static function (string $attribute, mixed $value, Closure $fail): void {
                if (! is_string($value) || trim($value) === '') {
                    return;
                }

                if (PhoneNumber::tryFormatFromRaw($value) === null) {
                    $fail('Введите номер полностью — формат +7 (XXX) XXX-XX-XX.');
                }
            },
        ];

        if ($required) {
            array_unshift($rules, 'required');
        } else {
            array_unshift($rules, 'nullable');
        }

        return $rules;
    }

    public static function formatState(?string $state): ?string
    {
        if ($state === null || trim($state) === '') {
            return null;
        }

        return PhoneNumber::tryFormatFromRaw($state) ?? trim($state);
    }

    public static function dehydrateState(?string $state): ?string
    {
        if ($state === null || trim($state) === '') {
            return null;
        }

        return PhoneNumber::tryFormatFromRaw($state);
    }
}
