<?php

namespace Tests\Feature\Crm;

use App\Mail\ClientWelcomeMail;
use Illuminate\Support\Facades\Mail;
use Tests\ApiTestCase;

final class ClientWelcomeMailTest extends ApiTestCase
{
    public function test_register_with_email_sends_welcome_mail(): void
    {
        Mail::fake();

        $email = 'welcome.'.bin2hex(random_bytes(4)).'@example.test';

        $this->postJson('/api/client/register', [
            'name' => 'Welcome User',
            'phone' => $this->uniquePhone(),
            'email' => $email,
            'password' => 'secret12',
            'consent_personal_data' => true,
        ])->assertCreated();

        Mail::assertSent(ClientWelcomeMail::class, function (ClientWelcomeMail $mail) use ($email): bool {
            return $mail->hasTo($email)
                && $mail->clientName === 'Welcome User';
        });
    }

    public function test_register_without_email_skips_welcome_mail(): void
    {
        Mail::fake();

        $this->postJson('/api/client/register', [
            'name' => 'No Email User',
            'phone' => $this->uniquePhone(),
            'password' => 'secret12',
            'consent_personal_data' => true,
        ])->assertCreated();

        Mail::assertNotSent(ClientWelcomeMail::class);
    }
}
