<?php

namespace App\Infrastructure\Client\Repository;

use App\Domain\Client\Entity\Client as ClientEntity;
use App\Domain\Client\Entity\ClientAddress as ClientAddressEntity;
use App\Domain\Client\Repository\ClientRepository as ClientRepositoryContract;
use App\Domain\Client\VO\Email;
use App\Domain\Client\VO\PhoneNumber;
use App\Infrastructure\Client\Model\UR_Client;
use App\Infrastructure\Client\Model\UR_ClientAddress;
use DateTimeImmutable;

class ClientRepository implements ClientRepositoryContract
{
    public function findById(int $id): ?ClientEntity
    {
        $model = UR_Client::with(['addresses' => function ($query) {
            $query->whereNull('deleted_at');
        }])->find($id);

        return $model ? $this->mapToEntity($model) : null;
    }

    public function findByPhone(string $phone): ?ClientEntity
    {
        $formatted = $this->formatPhoneForStorage($phone);

        $model = UR_Client::with(['addresses' => function ($query) {
            $query->whereNull('deleted_at');
        }])
            ->where('phone', $formatted)
            ->first();

        return $model ? $this->mapToEntity($model) : null;
    }

    public function findByEmail(string $email): ?ClientEntity
    {
        $model = UR_Client::with(['addresses' => function ($query) {
            $query->whereNull('deleted_at');
        }])
            ->where('email', $email)
            ->first();

        return $model ? $this->mapToEntity($model) : null;
    }

    public function existsByPhone(string $phone): bool
    {
        $formatted = $this->formatPhoneForStorage($phone);

        return UR_Client::where('phone', $formatted)->exists();
    }

    public function existsByEmail(string $email): bool
    {
        return UR_Client::where('email', $email)->exists();
    }

    public function save(ClientEntity $client): void
    {
        $model = $client->id()
            ? UR_Client::findOrFail($client->id())
            : new UR_Client();

        $model->name = $client->name();
        $model->phone = $this->formatPhoneForStorage((string) $client->phone());
        $model->email = $client->email() ? (string) $client->email() : null;
        $model->birth_date = $client->birthDate()?->format('Y-m-d');
        $model->password = $client->passwordHash();
        $model->status = $client->status();
        $model->consent_personal_data = $client->consentPersonalData();
        $model->consent_marketing = $client->consentMarketing();
        $model->default_address_id = $client->defaultAddressId();
        $model->deleted_at = $client->deletedAt()?->format('Y-m-d H:i:s');

        $model->save();

        // TODO: синхронизация адресов будет добавлена позже,
        // когда появится отдельный репозиторий для адресов.

        // Синхронизируем сгенерированный ID модели обратно в доменную сущность.
        if ($client->id() === null) {
            $client->assignPersistedId((int) $model->id);
        }
    }

    public function delete(ClientEntity $client): void
    {
        if ($client->id() === null) {
            return;
        }

        UR_Client::whereKey($client->id())->delete();
    }

    public function addAddress(int $clientId, ClientAddressEntity $address, bool $makeDefault): ClientEntity
    {
        $clientModel = UR_Client::findOrFail($clientId);

        $addressModel = new UR_ClientAddress();
        $addressModel->client_id = $clientId;
        $addressModel->type = $address->type();
        $addressModel->title = $address->title();
        $addressModel->street = $address->street();
        $addressModel->house = $address->house();
        $addressModel->entrance = $address->entrance();
        $addressModel->apartment = $address->apartment();
        $addressModel->save();

        if ($makeDefault) {
            $clientModel->default_address_id = $addressModel->id;
            $clientModel->save();
        }

        // Загружаем только не удалённые адреса (актуальный список для фронта).
        $clientModel->load(['addresses' => function ($query) {
            $query->whereNull('deleted_at');
        }]);

        return $this->mapToEntity($clientModel);
    }

    public function deleteAddress(int $clientId, int $addressId): ClientEntity
    {
        $clientModel = UR_Client::with('addresses')->findOrFail($clientId);

        /** @var UR_ClientAddress|null $addressModel */
        $addressModel = $clientModel->addresses()
            ->where('id', $addressId)
            ->first();

        if ($addressModel) {
            $wasDefault = $clientModel->default_address_id === $addressModel->id;
            $addressModel->delete();

            if ($wasDefault) {
                $newDefault = $clientModel->addresses()
                    ->whereKeyNot($addressId)
                    ->orderBy('created_at', 'desc')
                    ->first();

                $clientModel->default_address_id = $newDefault?->id;
                $clientModel->save();
            }
        }

        // Перезагружаем только не удалённые адреса
        $clientModel->load(['addresses' => function ($query) {
            $query->whereNull('deleted_at');
        }]);

        return $this->mapToEntity($clientModel);
    }

    public function setPasswordResetToken(string $email, string $token): void
    {
        $clientModel = UR_Client::where('email', $email)->first();

        if ($clientModel === null) {
            return;
        }

        $clientModel->password_reset_token = $token;
        $clientModel->password_reset_requested_at = now();
        $clientModel->save();
    }

    public function findByPasswordResetToken(string $token): ?ClientEntity
    {
        $clientModel = UR_Client::where('password_reset_token', $token)->first();

        return $clientModel ? $this->mapToEntity($clientModel) : null;
    }

    public function findByPasswordResetTokenRequestedAfter(string $token, DateTimeImmutable $requestedAfter): ?ClientEntity
    {
        $clientModel = UR_Client::where('password_reset_token', $token)
            ->where('password_reset_requested_at', '>=', $requestedAfter->format('Y-m-d H:i:s'))
            ->first();

        return $clientModel ? $this->mapToEntity($clientModel) : null;
    }

    public function clearPasswordResetToken(ClientEntity $client): void
    {
        if ($client->id() === null) {
            return;
        }

        $model = UR_Client::find($client->id());

        if ($model === null) {
            return;
        }

        $model->password_reset_token = null;
        $model->password_reset_requested_at = null;
        $model->save();
    }

    /**
     * Приводим телефон к формату, в котором он хранится в БД:
     * российский формат +7 (XXX) XXX-XX-XX.
     */
    private function formatPhoneForStorage(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '') {
            return $phone;
        }

        // Отрезаем ведущую 7/8, если номер приходит в формате 7XXXXXXXXXX / 8XXXXXXXXXX.
        if (strlen($digits) === 11 && in_array($digits[0], ['7', '8'], true)) {
            $digits = substr($digits, 1);
        }

        if (strlen($digits) !== 10) {
            return $phone;
        }

        $code = substr($digits, 0, 3);
        $part1 = substr($digits, 3, 3);
        $part2 = substr($digits, 6, 2);
        $part3 = substr($digits, 8, 2);

        return sprintf('+7 (%s) %s-%s-%s', $code, $part1, $part2, $part3);
    }

    private function mapToEntity(UR_Client $model): ClientEntity
    {
        $email = $model->email ? new Email($model->email) : null;
        $birthDate = $model->birth_date
            ? new DateTimeImmutable($model->birth_date)
            : null;
        $createdAt = new DateTimeImmutable($model->created_at);
        $updatedAt = new DateTimeImmutable($model->updated_at);
        $deletedAt = $model->deleted_at
            ? new DateTimeImmutable($model->deleted_at)
            : null;

        return ClientEntity::reconstitute(
            id: (int) $model->id,
            name: $model->name,
            phone: new PhoneNumber($model->phone),
            email: $email,
            birthDate: $birthDate,
            passwordHash: $model->password,
            status: $model->status,
            consentPersonalData: (bool) $model->consent_personal_data,
            consentMarketing: (bool) $model->consent_marketing,
            defaultAddressId: $model->default_address_id,
            addresses:
            $model->addresses
                ->map(fn ($addressModel) => $this->mapAddressToEntity($addressModel))
                ->all(),
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            deletedAt: $deletedAt,
        );
    }

    private function mapAddressToEntity($model): ClientAddressEntity
    {
        $entrance = $model->entrance ?? $model->staircase ?? null;

        return ClientAddressEntity::reconstitute(
            id: (int) $model->id,
            clientId: (int) $model->client_id,
            type: $model->type,
            title: $model->title,
            street: $model->street,
            house: $model->house,
            entrance: $entrance,
            apartment: $model->apartment,
            createdAt: new DateTimeImmutable($model->created_at),
            updatedAt: new DateTimeImmutable($model->updated_at),
        );
    }
}

