<?php

namespace NastyaKuznet\Blog\Service;

use NastyaKuznet\Blog\Model\User;
use NastyaKuznet\Blog\Service\interfaces\DatabaseServiceInterface;
use NastyaKuznet\Blog\Service\interfaces\UserServiceInterface;

class UserService implements UserServiceInterface
{
    private DatabaseServiceInterface $databaseService;

    public function __construct(DatabaseServiceInterface $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    public function get(int $userId): User
    {
        $userFromDB = $this->databaseService->getUserInfo($userId);
        return new User(
            $userFromDB["id"],
            $userFromDB["nickname"],
            $userFromDB["password"],
            $userFromDB["role_id"],
            $userFromDB["role_name"],
            $userFromDB["register_date"],
            $userFromDB["is_banned"]
        );
    }

    public function getAll(): array
    {
        $usersFromDb = $this->databaseService->getAllUsers();
        $users = [];
        foreach ($usersFromDb as $userData) {
            $users[] = new User(
                $userData["id"],
                $userData["nickname"],
                $userData["password"],
                $userData["role_id"],
                $userData["role_name"],
                $userData["register_date"],
                $userData["is_banned"]
            );
        }
        return $users;
    }
    
    public function changeRole(int $userId, int $newRoleId) : bool 
    {
        return $this->databaseService->changeUserRole($userId, $newRoleId);
    }

    public function delete(int $userId) : bool 
    {
        return $this->databaseService->deleteUser($userId);
    }

    public function toggleBan(int $userId, bool $isBanned): bool
    {
        return $this->databaseService->toggleUserBan($userId, $isBanned);
    }
}
