<?php

namespace NastyaKuznet\Blog\Service;

use NastyaKuznet\Blog\Model\User;
use NastyaKuznet\Blog\Service\DatabaseService;

class UserService 
{
    private DatabaseService $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    public function getUser(int $userId): User
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

    public function getAllUsers(): array
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
    
    public function changeUserRole($user_id, $new_role_id) : bool 
    {
        return $this->databaseService->changeUserRole($user_id, $new_role_id);
    }

    public function deleteUser(int $user_id) : bool 
    {
        return $this->databaseService->deleteUser($user_id);
    }
}
