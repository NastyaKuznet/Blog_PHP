<?php
namespace NastyaKuznet\Blog\Service\interfaces;

interface CommentServiceInterface
{
    public function update(int $id, string $newContent): bool;
    public function delete(int $id): bool;
    public function getById(int $id): ?array;
    public function getByPostId(int $postId): array;
    public function add(string $content, int $postId, int $userId): bool;
}