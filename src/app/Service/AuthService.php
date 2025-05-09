<?php

namespace NastyaKuznet\Blog\Service;

use NastyaKuznet\Blog\Model\User;
use NastyaKuznet\Blog\Model\Role;
use Exception;

class AuthService
{
    private array $config;
    private $db;
    private $roleMap = [
        1 => 'user',
        2 => 'writer',
        3 => 'moder',
        4 => 'admin'
    ];

    public function __construct(array $config, DatabaseService $db)
    {
        $this->config = $config;
        $this->db = $db;
    }

    /**
     * Регистрация нового пользователя
     *
     * @param string $username
     * @param string $password
     * @param string $roleName
     * @return bool
     */
    public function registerUser(string $username, string $password, string $roleName = 'user'): bool
    {
        // Получаем roleId по названию роли
        $roleId = array_search($roleName, $this->roleMap);
        if ($roleId === false) {
            throw new \InvalidArgumentException("Неизвестная роль: $roleName");
        }

        return $this->db->addUser($username, $password, $roleId);
    }

    /**
     * Проверка логина и пароля, возвращает объект User или false
     *
     * @param string $username
     * @param string $password
     * @return User|false
     */
    public function authenticateUser(string $username, string $password): mixed
    {
        $userData = $this->db->checkUser($username, $password);

        if (!$userData) {
            return false;
        }

        return new User(
            (int)$userData['id'],
            $userData['nickname'],
            $userData['password'],
            (int)$userData['role_id']
        );
    }

    /**
     * Генерация токена (пример на основе JWT)
     *
     * @param User $user
     * @param string $secretKey
     * @param int $ttl - время жизни токена в секундах
     * @return string
     */
    public function generateJwtToken(User $user, string $secretKey, int $ttl = 3600): string
    {
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT'
        ];

        $payload = [
            'iss' => 'blog-app', 
            'sub' => $user->id,
            'nickname' => $user->nickname,
            'role' => $this->getRoleNameById($user->roleId),
            'exp' => time() + $ttl,
        ];

        $base64UrlHeader = $this->base64UrlEncode(json_encode($header));
        $base64UrlPayload = $this->base64UrlEncode(json_encode($payload));

        $signature = hash_hmac('sha256', "$base64UrlHeader.$base64UrlPayload", $secretKey, true);
        $base64UrlSignature = $this->base64UrlEncode($signature);

        return "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";
    }

    /**
     * Получение названия роли по ID
     */
    private function getRoleNameById(int $roleId): string
    {
        return $this->roleMap[$roleId] ?? 'unknown';
    }

    /**
     * Base64UrlEncode
     */
    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64UrlDecode
     */
    public function base64UrlDecode(string $data): string
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

    /**
     * Получить данные из токена
     */
    public function decodeJwtToken(string $token, string $secretKey): ?array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null; // Неверный формат токена
        }   

        [$header, $payload, $signature] = explode('.', $token);

        $validSignature = hash_hmac('sha256', "$header.$payload", $secretKey, true);
        $validSignatureB64 = $this->base64UrlEncode($validSignature);

        if (!hash_equals($validSignatureB64, $signature)) {
            return null;
        }

        $payloadData = json_decode($this->base64UrlDecode($payload), true);

        if (isset($payloadData['exp']) && $payloadData['exp'] < time()) {
            return null;
        }

        return $payloadData;
    }
}