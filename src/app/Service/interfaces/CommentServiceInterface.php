<?php
namespace NastyaKuznet\Blog\Service\interfaces;

interface CommentServiceInterface
{
    public function updateComment(int $commentId, string $newContent): bool;
    public function deleteComment(int $commentId): bool;
    public function getCommentById(int $commentId): ?array;
    public function getCommentsByPostId(int $postId): array;
    public function addComment(string $content, int $postId, int $userId): bool;
}