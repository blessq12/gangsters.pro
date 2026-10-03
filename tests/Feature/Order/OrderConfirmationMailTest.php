<?php

namespace Tests\Feature\Order;

use App\Mail\OrderCreatedMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\ApiTestCase;

final class OrderConfirmationMailTest extends ApiTestCase
{
    public function test_place_order_with_client_email_sends_confirmation(): void
    {
        Mail::fake();

        $email = 'order.'.bin2hex(random_bytes(4)).'@example.test';
        $phone = $this->uniquePhone();

        $register = $this->postJson('/api/client/register', [
            'name' => 'Order Mail User',
            'phone' => $phone,
            'email' => $email,
            'password' => 'secret12',
            'consent_personal_data' => true,
        ])->assertCreated();

        $token = (string) $register->json('token');
        $clientId = (int) $register->json('client.id');
        $productId = $this->activeProductId();

        $placed = $this->placeOrderForClient($token, $clientId, $productId);
        $orderId = $placed['order_id'];

        Mail::assertSent(OrderCreatedMail::class, function (OrderCreatedMail $mail) use ($email, $orderId): bool {
            return $mail->hasTo($email)
                && $mail->orderId === $orderId
                && $mail->clientName === 'Order Mail User'
                && $mail->deliveryMethod === 'Самовывоз'
                && $mail->paymentMethod === 'Наличными'
                && $mail->lineSummaries !== [];
        });
    }

    public function test_place_order_without_email_skips_confirmation(): void
    {
        Mail::fake();

        $account = $this->registerClient();
        $this->assertNull(DB::table('CRM_clients')->where('id', $account['client']['id'])->value('email'));

        $this->placeOrderForClient(
            $account['token'],
            (int) $account['client']['id'],
            $this->activeProductId(),
        );

        Mail::assertNotSent(OrderCreatedMail::class);
    }
}
