<?php
namespace NastyaKuznet\Blog\Service\interfaces;

use NastyaKuznet\Blog\Model\User;

interface UserServiceInterface
{
    public function get(int $userId): User;
    public function getAll(): array;
    public function changeRole(int $userId, int $newRoleId) : bool;
    public function delete(int $userId): bool;
    public function toggleBan(int $userId, bool $isBanned): bool;
}