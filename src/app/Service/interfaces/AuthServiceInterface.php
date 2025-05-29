<?php
namespace NastyaKuznet\Blog\Service\interfaces;

use NastyaKuznet\Blog\Model\User;

interface AuthServiceInterface
{
    public function register(string $username, string $password): bool;
    public function checkRegistration(string $username): bool;
    public function authenticate(string $username, string $password): mixed;
    public function generateJwtToken(User $user, string $secretKey, int $ttl = 3600): string;
    public function decodeJwtToken(string $token, string $secretKey): ?array;
}