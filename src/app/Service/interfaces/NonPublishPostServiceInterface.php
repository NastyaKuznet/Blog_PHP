<?php
namespace NastyaKuznet\Blog\Service\interfaces;

use NastyaKuznet\Blog\Model\Post;

interface NonPublishPostServiceInterface
{
    public function getAllNonPublish(): array;
    public function getNonPublishById(int $id): ?Post;
}