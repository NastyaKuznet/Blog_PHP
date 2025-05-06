<?php

use FirebaseJWTJWT;

class AuthController {
    private $jwtSecret;

    public function __construct() {
        $this->jwtSecret = getenv('JWT_SECRET');
    }

    public function register($request, $response) {
        $data = $request->getParsedBody();
        if (!isset($data['username'], $data['password'], $data['role'])) {
            return $response->withStatus(400)->withJson(['error' => 'Invalid input']);
        }

        User::create($data['username'], $data['password'], $data['role']);
        return $response->withStatus(201)->withJson(['message' => 'User registered']);
    }

    public function login($request, $response) {
        $data = $request->getParsedBody();
        $user = User::findByUsername($data['username']);

        if ($user && password_verify($data['password'], $user['password'])) {
            $token = JWT::encode(['username' => $data['username'], 'role' => $user['role']], $this->jwtSecret);
            return $response->withJson(['token' => $token]);
        }

        return $response->withStatus(401)->withJson(['error' => 'Invalid credentials']);
    }
}

