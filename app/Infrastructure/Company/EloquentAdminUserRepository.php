<?php

namespace App\Infrastructure\Company;

use App\Application\Company\Contracts\AdminUserRepository;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

final class EloquentAdminUserRepository implements AdminUserRepository
{
    public function list(?string $search, int $page, int $perPage): array
    {
        $query = User::query()->orderByDesc('id');

        if (filled($search)) {
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('tel', 'like', '%'.$search.'%');
            });
        }

        $paginator = $query->paginate(perPage: $perPage, page: $page);

        return [
            'items' => collect($paginator->items())
                ->map(fn (User $user) => $this->toArray($user))
                ->values()
                ->all(),
            'total' => $paginator->total(),
        ];
    }

    public function findById(int $id): ?array
    {
        $user = User::query()->find($id);

        return $user !== null ? $this->toArray($user) : null;
    }

    public function create(array $data): array
    {
        $user = User::query()->create([
            'name' => (string) ($data['name'] ?? ''),
            'email' => (string) ($data['email'] ?? ''),
            'tel' => $data['tel'] ?? null,
            'dob' => $data['dob'] ?? null,
            'password' => Hash::make((string) ($data['password'] ?? '')),
        ]);

        return $this->toArray($user);
    }

    public function update(int $id, array $data): array
    {
        $user = User::query()->findOrFail($id);

        $payload = [
            'name' => $data['name'] ?? $user->name,
            'email' => $data['email'] ?? $user->email,
            'tel' => array_key_exists('tel', $data) ? $data['tel'] : $user->tel,
            'dob' => array_key_exists('dob', $data) ? $data['dob'] : $user->dob,
        ];

        if (filled($data['password'] ?? null)) {
            $payload['password'] = Hash::make((string) $data['password']);
        }

        $user->fill($payload);
        $user->save();

        return $this->toArray($user->fresh());
    }

    public function delete(int $id): void
    {
        User::query()->whereKey($id)->delete();
    }

    /**
     * @return array<string, mixed>
     */
    private function toArray(User $user): array
    {
        return [
            'id' => (int) $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'tel' => $user->tel,
            'dob' => $user->dob,
        ];
    }
}
