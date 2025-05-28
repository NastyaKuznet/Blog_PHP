<?php
namespace NastyaKuznet\Blog\Service\interfaces;

interface LikeServiceInterface
{
    public function check(int $postId, int $userId): bool;
    public function add(int $postId, int $userId): bool;
    public function delete(int $postId, int $userId): bool;
}