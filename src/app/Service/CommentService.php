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

    public function getByPostId(int $postId): array
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

    public function add(string $content, int $postId, int $userId): bool
    {
        return $this->databaseService->addComment($content, $postId, $userId);
    }

    public function update(int $id, string $newContent): bool
    {
        return $this->databaseService->updateComment($id, $newContent);
    }

    public function delete(int $id): bool
    {
        return $this->databaseService->deleteComment($id);
    }

    public function getById(int $id): ?array
    {
        return $this->databaseService->getCommentById($id);
    }
}