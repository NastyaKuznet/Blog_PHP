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

    public function getUser (int $userId) : User 
    {
        $userFromDB = $this->databaseService->getUserInfo($userId);
        return new User(
            $userFromDB["id"], 
            $userFromDB["nickname"],
            $userFromDB["password"],
            $userFromDB["role_id"],
            $userFromDB["role_name"]
        );
    }

    public function getAllUsers () : array 
    {
        $usersFromDb = $this->databaseService->getAllUsers();
        $users = [];
        foreach ($usersFromDb as $usersData) {
            $users[] = new User(
                $usersData["id"], 
                $usersData["nickname"],
                $usersData["password"],
                $usersData["role_id"],
                $usersData["role_name"]
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
