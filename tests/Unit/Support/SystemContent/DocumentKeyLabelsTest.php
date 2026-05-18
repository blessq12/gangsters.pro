<?php

namespace Tests\Unit\Support\SystemContent;

use App\Support\SystemContent\DocumentKeyLabels;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class DocumentKeyLabelsTest extends TestCase
{
    public function test_keys_contains_all_seeded_documents(): void
    {
        $this->assertSame(
            [
                DocumentKeyLabels::PRIVACY_POLICY,
                DocumentKeyLabels::TERMS_OF_USE,
                DocumentKeyLabels::USER_AGREEMENT,
            ],
            DocumentKeyLabels::keys()
        );
    }

    #[DataProvider('labelProvider')]
    public function test_label_for_known_key(string $key, string $expected): void
    {
        $this->assertSame($expected, DocumentKeyLabels::label($key));
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function labelProvider(): array
    {
        return [
            'privacy' => [DocumentKeyLabels::PRIVACY_POLICY, 'Политика конфиденциальности'],
            'terms' => [DocumentKeyLabels::TERMS_OF_USE, 'Правила использования'],
            'agreement' => [DocumentKeyLabels::USER_AGREEMENT, 'Пользовательское соглашение'],
        ];
    }
}
