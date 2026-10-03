<?php

namespace Tests\Feature\Crm;

use App\Mail\ClientPasswordResetMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\ApiTestCase;

final class ClientPasswordResetTest extends ApiTestCase
{
    public function test_forgot_password_sends_mail_and_updates_hash_for_known_email(): void
    {
        Mail::fake();

        $email = 'reset.'.bin2hex(random_bytes(4)).'@example.test';
        $oldPassword = 'secret12';
        $phone = $this->uniquePhone();

        $this->postJson('/api/client/register', [
            'name' => 'Reset User',
            'phone' => $phone,
            'email' => $email,
            'password' => $oldPassword,
            'consent_personal_data' => true,
        ])->assertCreated();

        $clientId = (int) DB::table('CRM_clients')->where('email', $email)->value('id');
        $this->assertGreaterThan(0, $clientId);
        $oldHash = (string) DB::table('CRM_clients')->where('id', $clientId)->value('password');

        $response = $this->postJson('/api/client/forgot-password', [
            'email' => $email,
        ]);

        $response->assertOk()
            ->assertJsonPath(
                'message',
                'Если такой аккаунт есть, мы отправили письмо с новым паролем.',
            );

        Mail::assertSent(ClientPasswordResetMail::class, function (ClientPasswordResetMail $mail) use ($email): bool {
            return $mail->hasTo($email)
                && is_string($mail->plainPassword)
                && strlen($mail->plainPassword) >= 8;
        });

        $newHash = (string) DB::table('CRM_clients')->where('id', $clientId)->value('password');
        $this->assertNotSame($oldHash, $newHash);

        $sent = null;
        Mail::assertSent(ClientPasswordResetMail::class, function (ClientPasswordResetMail $mail) use (&$sent): bool {
            $sent = $mail;

            return true;
        });
        $this->assertInstanceOf(ClientPasswordResetMail::class, $sent);
        $this->assertTrue(Hash::check($sent->plainPassword, $newHash));

        $this->postJson('/api/client/login', [
            'email' => $email,
            'password' => $oldPassword,
        ])->assertStatus(422);

        $this->postJson('/api/client/login', [
            'email' => $email,
            'password' => $sent->plainPassword,
        ])->assertOk()
            ->assertJsonStructure(['token', 'client' => ['id']]);
    }

    public function test_forgot_password_unknown_email_returns_ok_without_mail(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/client/forgot-password', [
            'email' => 'missing.'.bin2hex(random_bytes(4)).'@example.test',
        ]);

        $response->assertOk()
            ->assertJsonPath(
                'message',
                'Если такой аккаунт есть, мы отправили письмо с новым паролем.',
            );

        Mail::assertNothingSent();
    }

    public function test_forgot_password_requires_email(): void
    {
        $this->postJson('/api/client/forgot-password', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_forgot_password_mail_failure_keeps_old_password(): void
    {
        Mail::shouldReceive('to')
            ->once()
            ->andThrow(new TransportException('SMTP down'));

        $email = 'reset-fail.'.bin2hex(random_bytes(4)).'@example.test';
        $oldPassword = 'secret12';
        $phone = $this->uniquePhone();

        $this->postJson('/api/client/register', [
            'name' => 'Reset Fail User',
            'phone' => $phone,
            'email' => $email,
            'password' => $oldPassword,
            'consent_personal_data' => true,
        ])->assertCreated();

        $clientId = (int) DB::table('CRM_clients')->where('email', $email)->value('id');
        $oldHash = (string) DB::table('CRM_clients')->where('id', $clientId)->value('password');

        $this->postJson('/api/client/forgot-password', [
            'email' => $email,
        ])->assertStatus(503)
            ->assertJsonPath('message', 'Не удалось отправить письмо. Попробуй позже.');

        $newHash = (string) DB::table('CRM_clients')->where('id', $clientId)->value('password');
        $this->assertSame($oldHash, $newHash);

        $this->postJson('/api/client/login', [
            'email' => $email,
            'password' => $oldPassword,
        ])->assertOk();
    }
}
