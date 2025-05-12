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

    public function getAllUsers (int $userId) : array 
    {
        $usersFromDb = $this->databaseService->getAllUsers($userId);
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
        
}

//мне надо сделать новый контроллер UsersAdminController например, это для админа, то есть у нас контроллер обращается к сервису, и сервис методами из дбСервиса все делает
//тоже взять html шаблон, тоже сделать твиг, и передать в него параметры правильные, посмотреть как сделали с настей все

//не забывать протягивать зависимости через "Blog_PHP\src\app\config\dependencies.php" - это понадобится только для контроллера UsersAdminController, нужно будет протянуть чтоб был доступ к нужному сервису