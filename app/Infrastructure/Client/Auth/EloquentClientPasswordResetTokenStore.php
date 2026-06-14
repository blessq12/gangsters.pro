<?php

namespace App\Infrastructure\Client\Auth;

use App\Domain\Client\Port\ClientPasswordResetTokenStorePort;
use App\Infrastructure\Client\Model\CLN_PasswordResetToken;
use Illuminate\Support\Facades\Hash;

final class EloquentClientPasswordResetTokenStore implements ClientPasswordResetTokenStorePort
{
    public function store(string $email, string $plainToken): void
    {
        CLN_PasswordResetToken::query()->updateOrCreate(
            ['email' => mb_strtolower($email)],
            [
                'token' => Hash::make($plainToken),
                'created_at' => now(),
            ],
        );
    }

    public function resolveEmailByToken(string $plainToken): ?string
    {
        $ttlMinutes = (int) config('auth.password_reset_token_ttl_minutes', 30);
        $rows = CLN_PasswordResetToken::query()->get();

        foreach ($rows as $row) {
            if (! Hash::check($plainToken, (string) $row->token)) {
                continue;
            }

            if ($row->created_at !== null && $row->created_at->lt(now()->subMinutes($ttlMinutes))) {
                $this->delete((string) $row->email);

                return null;
            }

            return (string) $row->email;
        }

        return null;
    }

    public function delete(string $email): void
    {
        CLN_PasswordResetToken::query()
            ->where('email', mb_strtolower($email))
            ->delete();
    }
}
