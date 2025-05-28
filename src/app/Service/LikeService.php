<?php

namespace NastyaKuznet\Blog\Service;

use NastyaKuznet\Blog\Service\interfaces\DatabaseServiceInterface;
use NastyaKuznet\Blog\Service\interfaces\LikeServiceInterface;

class LikeService implements LikeServiceInterface
{
    private DatabaseServiceInterface $databaseService;

    public function __construct(DatabaseServiceInterface $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    public function check(int $postId, int $userId): bool 
    {
        return $this->databaseService->checkLikeByPostIdAndUserId($postId, $userId);
    }

    public function add(int $postId, int $userId): bool 
    {
        return $this->databaseService->addLike($postId, $userId);
    }

    public function delete(int $postId, int $userId): bool
    {
        return $this->databaseService->deleteLike($postId, $userId);
    }
}