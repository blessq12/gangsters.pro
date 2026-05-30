<?php

namespace Tests\Unit\Filament\Operations;

use App\Filament\Operations\Support\FilamentActiveCartDetailMapper;
use PHPUnit\Framework\TestCase;

final class FilamentActiveCartDetailMapperTest extends TestCase
{
    public function test_maps_snapshot_to_flat_form_state(): void
    {
        $state = FilamentActiveCartDetailMapper::toFormState([
            'session' => [
                'id' => 10,
                'public_id' => 'pub-10',
                'created_at' => '01.01.2026 12:00',
                'updated_at' => '02.01.2026 10:00',
                'expires_at' => '09.01.2026 10:00',
            ],
            'client' => [
                'type_label' => 'Гость',
                'label' => 'Гость',
                'badge_color' => 'gray',
                'id' => null,
                'phone' => null,
                'email' => null,
            ],
            'cart' => [
                'is_empty' => false,
                'lines_count' => 1,
                'total_quantity' => 2,
                'lines' => [[
                    'product_id' => 7,
                    'product_name' => 'Ролл',
                    'quantity' => 2,
                    'unit_price_label' => '350,00 ₽',
                    'line_total_label' => '700,00 ₽',
                ]],
            ],
            'favorites' => [
                'count' => 1,
                'items' => [['product_id' => 3, 'product_name' => 'Суп']],
            ],
            'checkout' => [
                'has_draft' => true,
                'sections' => [[
                    'title' => 'Доставка',
                    'rows' => [
                        ['label' => 'Способ', 'value' => 'Курьер'],
                        ['label' => 'Адрес', 'value' => 'Ленина, д. 1'],
                    ],
                ]],
            ],
        ]);

        $this->assertFalse($state['load_error']);
        $this->assertSame('pub-10', $state['session_public_id']);
        $this->assertSame('1 поз. · 2 шт.', $state['cart_summary']);
        $this->assertCount(1, $state['cart_lines']);
        $this->assertSame(0, array_key_first($state['cart_lines']));
        $this->assertSame('Курьер', $state['checkout_delivery']['Способ']);
        $this->assertTrue($state['has_checkout_draft']);
        $this->assertSame('Начат', $state['checkout_draft_status']);
        $this->assertArrayNotHasKey('checkout_raw_json', $state);
    }

    public function test_reindexes_cart_lines_with_string_keys(): void
    {
        $state = FilamentActiveCartDetailMapper::toFormState([
            'session' => ['id' => 1, 'public_id' => 'p', 'created_at' => '', 'updated_at' => '', 'expires_at' => ''],
            'client' => ['type_label' => 'Гость', 'label' => 'Гость', 'badge_color' => 'gray', 'id' => null, 'phone' => null, 'email' => null],
            'cart' => [
                'is_empty' => false,
                'lines_count' => 1,
                'total_quantity' => 1,
                'lines' => [
                    'row-a' => [
                        'product_id' => 9,
                        'product_name' => 'Сет',
                        'quantity' => 1,
                        'unit_price_label' => null,
                        'line_total_label' => null,
                    ],
                ],
            ],
            'favorites' => ['count' => 0, 'items' => []],
            'checkout' => ['has_draft' => false, 'sections' => []],
        ]);

        $this->assertSame([0], array_keys($state['cart_lines']));
        $this->assertSame('Сет', $state['cart_lines'][0]['product_name']);
    }

    public function test_load_error_state(): void
    {
        $state = FilamentActiveCartDetailMapper::loadError('Shopping-сессия не найдена.');

        $this->assertTrue($state['load_error']);
        $this->assertSame('Shopping-сессия не найдена.', $state['load_error_message']);
        $this->assertSame([], $state['cart_lines']);
    }
}
