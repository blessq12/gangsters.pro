<?php

namespace App\Legacy\Console\Commands;

use App\Infrastructure\Client\Model\UR_Client;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateUsersToClients extends Command
{
    protected $signature = 'clients:migrate-from-users {--dry-run : Только показать, что будет мигрировано, без записи в БД}';

    protected $description = 'Мигрировать клиентов из legacy-таблицы users в доменную таблицу UR_clients';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $this->info('Старт миграции пользователей из users в UR_clients');
        if ($dryRun) {
            $this->warn('Режим dry-run: данные НЕ будут записаны в UR_clients');
        }

        $existingClientsByEmail = UR_Client::query()
            ->whereNotNull('email')
            ->pluck('id', 'email')
            ->all();

        $existingClientsByPhone = UR_Client::query()
            ->whereNotNull('phone')
            ->pluck('id', 'phone')
            ->all();

        $users = User::query()->get();

        if ($users->isEmpty()) {
            $this->info('В таблице users нет записей для миграции.');

            return self::SUCCESS;
        }

        $this->info(sprintf('Найдено %d пользователей в таблице users.', $users->count()));

        $created = 0;
        $skipped = 0;

        DB::beginTransaction();

        try {
            foreach ($users as $user) {
                /** @var \App\Models\User $user */

                $email = $user->email ?: null;
                $tel = $user->tel ?: null;

                $phoneFormatted = $tel ? $this->formatPhoneForStorage($tel) : null;

                // Проверяем, нет ли уже клиента с таким email/телефоном
                $conflictEmail = $email && array_key_exists($email, $existingClientsByEmail);
                $conflictPhone = $phoneFormatted && array_key_exists($phoneFormatted, $existingClientsByPhone);

                if ($conflictEmail || $conflictPhone) {
                    $reasons = [];
                    if ($conflictEmail) {
                        $reasons[] = sprintf('дублирующий email=%s', $email);
                    }
                    if ($conflictPhone) {
                        $reasons[] = sprintf('дублирующий телефон=%s', $phoneFormatted);
                    }

                    $this->warn(sprintf(
                        'Пропуск пользователя users#%d (%s): %s',
                        $user->id,
                        $user->email ?? $user->name,
                        implode('; ', $reasons)
                    ));
                    $skipped++;
                    continue;
                }

                if ($dryRun) {
                    $this->line(sprintf(
                        '[dry-run] Создали бы UR_client для пользователя #%d (%s), phone=%s, email=%s',
                        $user->id,
                        $user->name,
                        $phoneFormatted ?? 'NULL',
                        $email ?? 'NULL',
                    ));
                    $created++;
                    continue;
                }

                $client = new UR_Client();
                $client->name = $user->name ?? 'Без имени';
                $client->phone = $phoneFormatted ?? '';
                $client->email = $email;
                $client->birth_date = $user->dob ?: null;
                $client->password = $user->password ?? null;
                $client->status = 'active';
                $client->consent_personal_data = false;
                $client->consent_marketing = false;
                $client->default_address_id = null;
                $client->created_at = $user->created_at ?? now();
                $client->updated_at = $user->updated_at ?? now();
                $client->save();

                $existingClientsByEmail[$client->email] = $client->id;
                if ($client->phone !== '') {
                    $existingClientsByPhone[$client->phone] = $client->id;
                }

                $created++;
            }

            if ($dryRun) {
                DB::rollBack();
            } else {
                DB::commit();
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Ошибка во время миграции: ' . $e->getMessage());
            report($e);

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Миграция завершена. Создано клиентов: %d, пропущено: %d.',
            $created,
            $skipped,
        ));

        return self::SUCCESS;
    }

    /**
     * Приводим телефон к формату +7 (XXX) XXX-XX-XX.
     */
    private function formatPhoneForStorage(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '') {
            return null;
        }

        if (strlen($digits) === 11 && in_array($digits[0], ['7', '8'], true)) {
            $digits = substr($digits, 1);
        }

        if (strlen($digits) !== 10) {
            return null;
        }

        $code = substr($digits, 0, 3);
        $part1 = substr($digits, 3, 3);
        $part2 = substr($digits, 6, 2);
        $part3 = substr($digits, 8, 2);

        return sprintf('+7 (%s) %s-%s-%s', $code, $part1, $part2, $part3);
    }
}
