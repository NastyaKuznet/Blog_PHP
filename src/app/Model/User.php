<?php

namespace NastyaKuznet\Blog\Model;

class User
{ 
    public function __construct(
        public int $id, 
        public string $login, 
        public string $password, 
        public int $roleId, 
        public string $roleName, 
        public string $registerDate, 
        public bool $isBanned
    ) {
    }
}