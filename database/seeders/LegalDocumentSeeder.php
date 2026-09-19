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
                    'Черновик публичной оферты. Требует юридической вычитки перед публикацией в продакшен. Документ регулирует условия заказа и доставки готовой еды через сайт.'
                ),
            ],
            LegalDocumentType::TERMS => [
                'title' => LegalDocumentType::LABELS[LegalDocumentType::TERMS],
                'body_html' => $this->draftBody(
                    'Пользовательское соглашение',
                    'Черновик пользовательского соглашения. Требует юридической вычитки. Описывает правила использования сайта и личного кабинета.'
                ),
            ],
            LegalDocumentType::PDN_CONSENT => [
                'title' => LegalDocumentType::LABELS[LegalDocumentType::PDN_CONSENT],
                'body_html' => $this->draftBody(
                    'Согласие на обработку персональных данных',
                    'Черновик отдельного согласия субъекта на обработку персональных данных. Требует юридической вычитки. Согласие даётся при оформлении заказа и регистрации.'
                ),
            ],
            LegalDocumentType::COOKIES => [
                'title' => LegalDocumentType::LABELS[LegalDocumentType::COOKIES],
                'body_html' => $this->draftBody(
                    'Политика использования cookie',
                    'Черновик политики cookie. Требует юридической вычитки. Сайт использует необходимые cookie для работы сессии и, при согласии, аналитические cookie (Яндекс.Метрика и др.).'
                ),
            ],
            LegalDocumentType::SELLER_INFO => [
                'title' => LegalDocumentType::LABELS[LegalDocumentType::SELLER_INFO],
                'body_html' => $this->draftBody(
                    'Информация о товаре, доставке и возврате',
                    'Черновик сведений для потребителя (ЗоЗПП). Требует юридической вычитки. Здесь описываются характеристики готовой еды, условия доставки/самовывоза, сроки, отказ от заказа до передачи и порядок претензий. Операционные детали также см. на странице «Оплата и доставка».'
                ),
            ],
        ];

        foreach ($documents as $type => $payload) {
            if (LegalDocument::query()->where('type', $type)->where('is_current', true)->exists()) {
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
            'Черновик политики конфиденциальности. Файл privacy.html не найден.'
        );
    }

    private function draftBody(string $heading, string $text): string
    {
        return <<<HTML
<div class="space-y-4">
    <p class="bg-amber-50 border-l-4 border-amber-500 p-4 text-amber-800"><strong>Черновик.</strong> Требует юридической вычитки.</p>
    <h1 class="text-3xl font-bold text-gray-900">{$heading}</h1>
    <p class="text-gray-700">{$text}</p>
</div>
HTML;
    }
}
