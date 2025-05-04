<?php

namespace NastyaKuznet\Blog\Model;

class User
{
    public $id;
    public $nickname;
    public $password;
    public $roleId;

    public function __construct(int $id, string $nickname, string $password, int $roleId)
    {
        $this->id = $id;
        $this->nickname = $nickname;
        $this->password = $password;
        $this->roleId = $roleId;
    }
}
