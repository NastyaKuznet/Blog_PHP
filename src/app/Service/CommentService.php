<?php

namespace NastyaKuznet\Blog\Service;

use NastyaKuznet\Blog\Service\DatabaseService;

class CommentService
{
    private $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    public function updateComment(int $commentId, string $newContent): bool
    {
        return $this->databaseService->updateComment($commentId, $newContent);
    }

    public function deleteComment(int $commentId): bool
    {
        return $this->databaseService->deleteComment($commentId);
    }

    public function getCommentById(int $commentId): ?array
    {
        return $this->databaseService->getCommentById($commentId);
    }
}