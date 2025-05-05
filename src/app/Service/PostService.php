<?php

namespace NastyaKuznet\Blog\Service;

use NastyaKuznet\Blog\Service\DatabaseService;
use NastyaKuznet\Blog\Model\Post;

class PostService
{
    private array $config;
    private DatabaseService $databaseService;

    public function __construct(array $config, DatabaseService $databaseService)
    {
        $this->config = $config;
        $this->databaseService = $databaseService;
    }

    public function getAllPosts(): array
    {
        $postsFromDb = $this->databaseService->getAllPosts();
        $posts = [];
        foreach ($postsFromDb as $postData) {
            $posts[] = new Post(
                $postData['id'],
                $postData['title'],
                $postData['content'],
                $postData['likes'],
                $postData['user_id'],
                $postData['comment_count'],
                $postData['created_at']
            );
        }
        return $posts;
    }

    public function getAuthorName(int $userId): string
    {
        foreach ($this->config['users'] as $user) {
            if ($user['id'] === $userId) {
                return $user['nickname'];
            }
        }
        return 'Unknown Author';
    }

    public function getPostById(int $id): ?Post
    {
        foreach ($this->config['posts'] as $postData) {
            if ($postData['id'] === $id) {
                return new Post(
                    $postData['id'],
                    $postData['title'],
                    $postData['content'],
                    $postData['likes'],
                    $postData['userId'],
                    '',
                    0
                );

            }
        }
        return null;
    }

    public function filterByAuthorNickname(array $posts, string $nickname): array
    {
        $filteredPosts = [];
        foreach ($posts as $post) {
            $authorName = $this->getAuthorName($post->userId);
            if (stripos($authorName, $nickname) !== false) {  // Используем stripos для регистронезависимого поиска
                $filteredPosts[] = $post;
            }
        }
        return $filteredPosts;
    }

    public function incrementLike(int $postId): void
    {
        if (isset($this->config['posts'][$postId])) {
            $this->config['posts'][$postId]['likes']++;
        }
    }
}
