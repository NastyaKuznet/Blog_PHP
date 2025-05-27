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

    public function getUser(int $userId): User
    {
        $userFromDB = $this->databaseService->getUserInfo($userId);
        return new User(
            $userFromDB["id"],
            $userFromDB["login"],
            $userFromDB["password"],
            $userFromDB["role_id"],
            $userFromDB["role_name"],
            $userFromDB["register_date"],
            $userFromDB["is_banned"]
        );
    }

    public function getAllUsers(): array
    {
        $usersFromDb = $this->databaseService->getAllUsers();
        $users = [];
        foreach ($usersFromDb as $userData) {
            $users[] = new User(
                $userData["id"],
                $userData["login"],
                $userData["password"],
                $userData["role_id"],
                $userData["role_name"],
                $userData["register_date"],
                $userData["is_banned"]
            );
        }
        return $users;
    }
    
    public function changeUserRole($user_id, $new_role_id) : bool 
    {
        return $this->databaseService->changeUserRole($user_id, $new_role_id);
    }

    public function toggleUserBan(int $userId, bool $isBanned): bool
    {
        return $this->databaseService->toggleUserBan($userId, $isBanned);
    }
}
