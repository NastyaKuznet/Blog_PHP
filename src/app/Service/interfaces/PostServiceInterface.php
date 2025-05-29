<?php
namespace NastyaKuznet\Blog\Service\interfaces;

use NastyaKuznet\Blog\Model\Post;

interface PostServiceInterface
{
    public function getAll(?string $sortBy, ?string $order, ?string $authorNickname, ?string $tag): array;
    public function getById(int $id): ?Post;
    public function edit(int $id, string $title, string $preview, string $content, int $userId, array $tags): bool;
    public function publish(int $id): bool;
    public function delete(int $id): bool;
    public function getCountPostsByUserId(int $userId): int;
    public function getByUserId(int $userId): array;
    public function add(string $title, string $preview, string $content, int $userId, array $tags): bool;
}
