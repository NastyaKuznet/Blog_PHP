<?php

class User {
    private static $users = [];

    public static function create($username, $password, $role) {
        self::$users[$username] = [
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
        ];
        return true;
    }

    public static function findByUsername($username) {
        return self::$users[$username] ?? null;
    }

    public static function getAllUsers() {
        return self::$users;
    }
}

