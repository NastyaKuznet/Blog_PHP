<?php
namespace NastyaKuznet\Blog\Service\interfaces;

use NastyaKuznet\Blog\Model\Post;

interface LikeServiceInterface
{
    public function check(int $postId, int $userId): bool;
    public function add(int $postId, int $userId): bool;
    public function delete(int $postId, int $userId): bool;
}