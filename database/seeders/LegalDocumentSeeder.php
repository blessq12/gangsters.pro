<?php

namespace Database\Seeders;

use App\Enums\LegalDocumentType;
use App\Models\LegalDocument;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class LegalDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $documents = [
            LegalDocumentType::PRIVACY => [
                'title' => LegalDocumentType::LABELS[LegalDocumentType::PRIVACY],
                'body_html' => $this->privacyBody(),
            ],
            LegalDocumentType::OFFER => [
                'title' => LegalDocumentType::LABELS[LegalDocumentType::OFFER],
                'body_html' => $this->draftBody(
                    'Публичная оферта',
                    'Документ регулирует условия заказа и доставки готовой еды через сайт.'
                ),
            ],
            LegalDocumentType::TERMS => [
                'title' => LegalDocumentType::LABELS[LegalDocumentType::TERMS],
                'body_html' => $this->draftBody(
                    'Пользовательское соглашение',
                    'Описывает правила использования сайта и личного кабинета.'
                ),
            ],
            LegalDocumentType::PDN_CONSENT => [
                'title' => LegalDocumentType::LABELS[LegalDocumentType::PDN_CONSENT],
                'body_html' => $this->draftBody(
                    'Согласие на обработку персональных данных',
                    'Согласие субъекта на обработку персональных данных даётся при оформлении заказа и регистрации.'
                ),
            ],
            LegalDocumentType::COOKIES => [
                'title' => LegalDocumentType::LABELS[LegalDocumentType::COOKIES],
                'body_html' => $this->draftBody(
                    'Политика использования cookie',
                    'Сайт использует необходимые cookie для работы сессии и, при согласии, аналитические cookie (Яндекс.Метрика и др.).'
                ),
            ],
            LegalDocumentType::SELLER_INFO => [
                'title' => LegalDocumentType::LABELS[LegalDocumentType::SELLER_INFO],
                'body_html' => $this->draftBody(
                    'Информация о товаре, доставке и возврате',
                    'Сведения для потребителя: характеристики готовой еды, условия доставки и самовывоза, сроки, отказ от заказа до передачи и порядок претензий. Операционные детали также см. на странице «Оплата и доставка».'
                ),
            ],
        ];

        foreach ($documents as $type => $payload) {
            $existing = LegalDocument::query()->where('type', $type)->where('is_current', true)->first();
            if ($existing) {
                continue;
            }

            LegalDocument::query()->create([
                'type' => $type,
                'version' => 1,
                'title' => $payload['title'],
                'body_html' => $payload['body_html'],
                'is_current' => true,
                'published_at' => $now,
            ]);
        }
    }

    private function privacyBody(): string
    {
        $path = database_path('seeders/data/privacy.html');

        if (is_file($path)) {
            return (string) file_get_contents($path);
        }

        return $this->draftBody(
            'Политика конфиденциальности',
            'Политика в отношении обработки персональных данных.'
        );
    }

    private function draftBody(string $heading, string $text): string
    {
        return <<<HTML
<div class="space-y-4">
    <h1 class="text-3xl font-bold text-gray-900">{$heading}</h1>
    <p class="text-gray-700">{$text}</p>
</div>
HTML;
    }
}
