<?php

namespace NastyaKuznet\Blog\Model;

class User
{
    public $id;
    public $nickname;
    public $password;
    public $roleId;
    public $roleName;

    public function __construct(int $id, string $nickname, string $password, int $roleId, string $roleName)
    {
        $this->id = $id;
        $this->nickname = $nickname;
        $this->password = $password;
        $this->roleId = $roleId;
        $this->roleName = $roleName;
    }
}