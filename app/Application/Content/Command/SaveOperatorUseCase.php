<?php

namespace App\Application\Content\Command;

use App\Domain\Content\Repository\OperatorRepository;

final class SaveOperatorUseCase
{
    public function __construct(
        private readonly OperatorRepository $operators,
    ) {}

    /**
     * @return array{id: int, name: string, email: string}
     */
    public function execute(?int $id, string $name, string $email, ?string $password): array
    {
        $name = trim($name);
        $email = trim($email);
        if ($name === '' || $email === '') {
            throw new \InvalidArgumentException('Нужны имя и email.');
        }
        if ($password !== null && $password !== '' && strlen($password) < 8) {
            throw new \InvalidArgumentException('Пароль короче 8 символов.');
        }

        $operator = $this->operators->save($id, $name, $email, $password);

        return [
            'id' => $operator->id(),
            'name' => $operator->name(),
            'email' => $operator->email(),
        ];
    }
}
