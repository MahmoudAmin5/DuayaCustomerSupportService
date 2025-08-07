<?php
namespace App\Repositories\Interfaces;
use App\Models\User;
use Illuminate\Support\Collection;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;

    public function findByEmail(string $email): ?User;

    public function findByPhone(string $phone): ?User;

    public function getFirstAvailableAgent(): ?User;

    public function create(array $data): User;

    public function update(User $user, array $data): bool;

    public function delete(User $user): bool;

    public function all(): Collection;
    public function firstOrCreateByPhone(string $phone , string $name): User;
}
