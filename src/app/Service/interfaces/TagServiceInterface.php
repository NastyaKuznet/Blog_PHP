<?php
namespace NastyaKuznet\Blog\Service\interfaces;

use NastyaKuznet\Blog\Model\Post;

interface TagServiceInterface
{
    public function getByPostId(int $postId): array;
    public function getAll(): array;
}