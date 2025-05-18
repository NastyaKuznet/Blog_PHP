<?php

namespace NastyaKuznet\Blog\Model;

class User
{
    public $id;
    public $nickname;
    public $password;
    public $roleId;
    public $roleName;
    public $registerDate; 
    public $isBanned;     

    public function __construct(int $id, string $nickname, string $password, int $roleId, string $roleName, string $registerDate, bool $isBanned) 
    {
        $this->id = $id;
        $this->nickname = $nickname;
        $this->password = $password;
        $this->roleId = $roleId;
        $this->roleName = $roleName;
        $this->registerDate = $registerDate;
        $this->isBanned = $isBanned;
    }
}