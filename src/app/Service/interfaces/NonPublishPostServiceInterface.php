<?php
namespace NastyaKuznet\Blog\Service\interfaces;

use NastyaKuznet\Blog\Model\Post;

interface NonPublishPostServiceInterface
{
    public function getAll(): array;
    public function getById(int $id): ?Post;
}