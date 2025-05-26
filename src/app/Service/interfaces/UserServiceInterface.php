<?php
namespace NastyaKuznet\Blog\Service\interfaces;

use NastyaKuznet\Blog\Model\User;

interface UserServiceInterface
{
    public function getUser(int $userId): User;
    public function getAllUsers(): array;
    public function changeUserRole($user_id, $new_role_id) : bool;
    public function deleteUser(int $user_id): bool;
    public function toggleUserBan(int $userId, bool $isBanned): bool;
}