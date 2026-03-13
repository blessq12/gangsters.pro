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
        $model = UR_Client::with('addresses')->find($id);

        return $model ? $this->mapToEntity($model) : null;
    }

    public function findByPhone(string $phone): ?ClientEntity
    {
        $formatted = $this->formatPhoneForStorage($phone);

        $model = UR_Client::with('addresses')
            ->where('phone', $formatted)
            ->first();

        return $model ? $this->mapToEntity($model) : null;
    }

    public function findByEmail(string $email): ?ClientEntity
    {
        $model = UR_Client::with('addresses')
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
            $ref = new \ReflectionClass($client);
            $prop = $ref->getProperty('id');
            $prop->setAccessible(true);
            $prop->setValue($client, $model->id);
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
        $addressModel->liter = $address->liter();
        $addressModel->staircase = $address->staircase();
        $addressModel->apartment = $address->apartment();
        $addressModel->entrance_code = $address->entranceCode();
        $addressModel->floor = $address->floor();
        $addressModel->comment = $address->comment();
        $addressModel->save();

        if ($makeDefault) {
            $clientModel->default_address_id = $addressModel->id;
            $clientModel->save();
        }

        $clientModel->load('addresses');

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

        $clientModel->load('addresses');

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

        // Восстанавливаем сущность через рефлексию, чтобы не ломать инварианты конструктора.
        $ref = new \ReflectionClass(ClientEntity::class);
        /** @var ClientEntity $client */
        $client = $ref->newInstanceWithoutConstructor();

        $this->setProperty($client, 'id', $model->id);
        $this->setProperty($client, 'name', $model->name);
        $this->setProperty($client, 'phone', new PhoneNumber($model->phone));
        $this->setProperty($client, 'email', $email);
        $this->setProperty($client, 'birthDate', $birthDate);
        $this->setProperty($client, 'passwordHash', $model->password);
        $this->setProperty($client, 'status', $model->status);
        $this->setProperty($client, 'consentPersonalData', (bool) $model->consent_personal_data);
        $this->setProperty($client, 'consentMarketing', (bool) $model->consent_marketing);
        $this->setProperty($client, 'defaultAddressId', $model->default_address_id);
        $this->setProperty(
            $client,
            'addresses',
            $model->addresses
                ->map(fn ($addressModel) => $this->mapAddressToEntity($addressModel))
                ->all()
        );
        $this->setProperty($client, 'createdAt', $createdAt);
        $this->setProperty($client, 'updatedAt', $updatedAt);
        $this->setProperty($client, 'deletedAt', $deletedAt);

        return $client;
    }

    /**
     * @param ClientEntity $client
     * @param string $property
     * @param mixed $value
     */
    private function setProperty(ClientEntity $client, string $property, mixed $value): void
    {
        $ref = new \ReflectionProperty(ClientEntity::class, $property);
        $ref->setAccessible(true);
        $ref->setValue($client, $value);
    }

    private function mapAddressToEntity($model): ClientAddressEntity
    {
        $ref = new \ReflectionClass(ClientAddressEntity::class);
        /** @var ClientAddressEntity $address */
        $address = $ref->newInstanceWithoutConstructor();

        $this->setAddressProperty($address, 'id', $model->id);
        $this->setAddressProperty($address, 'clientId', $model->client_id);
        $this->setAddressProperty($address, 'type', $model->type);
        $this->setAddressProperty($address, 'title', $model->title);
        $this->setAddressProperty($address, 'street', $model->street);
        $this->setAddressProperty($address, 'house', $model->house);
        $this->setAddressProperty($address, 'liter', $model->liter);
        $this->setAddressProperty($address, 'staircase', $model->staircase);
        $this->setAddressProperty($address, 'apartment', $model->apartment);
        $this->setAddressProperty($address, 'entranceCode', $model->entrance_code);
        $this->setAddressProperty($address, 'floor', $model->floor);
        $this->setAddressProperty($address, 'comment', $model->comment);
        $this->setAddressProperty($address, 'createdAt', new DateTimeImmutable($model->created_at));
        $this->setAddressProperty($address, 'updatedAt', new DateTimeImmutable($model->updated_at));

        return $address;
    }

    private function setAddressProperty(ClientAddressEntity $address, string $property, mixed $value): void
    {
        $ref = new \ReflectionProperty(ClientAddressEntity::class, $property);
        $ref->setAccessible(true);
        $ref->setValue($address, $value);
    }
}

