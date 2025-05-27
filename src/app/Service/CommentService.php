<?php

namespace NastyaKuznet\Blog\Service;

use NastyaKuznet\Blog\Service\interfaces\DatabaseServiceInterface;
use NastyaKuznet\Blog\Service\interfaces\CommentServiceInterface;
use NastyaKuznet\Blog\Model\Comment;

class CommentService implements CommentServiceInterface
{
    private DatabaseServiceInterface $databaseService;

    public function __construct(DatabaseServiceInterface $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    public function getCommentsByPostId(int $postId): array
    {
        $commentsFromDb = $this->databaseService->getCommentsByPostId($postId);
        $comments = [];
        foreach ($commentsFromDb as $commentData) {
            $comments[] = new Comment(
                $commentData['id'],
                $commentData['content'],
                $commentData['post_id'],
                $commentData['user_id'],
                $commentData['user_login'],
                $commentData['created_date'],
                $commentData['edit_date'],
                $commentData['delete_date'],
                $commentData['is_edit'],
                $commentData['is_delete'] 
            );
        }
        return $comments;
    }

    public function addComment(string $content, int $postId, int $userId): bool
    {
        return $this->databaseService->addComment($content, $postId, $userId);
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