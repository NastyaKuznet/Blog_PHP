<?php

namespace NastyaKuznet\Blog\Service;

use NastyaKuznet\Blog\Service\interfaces\DatabaseServiceInterface;
use NastyaKuznet\Blog\Model\Post;
use NastyaKuznet\Blog\Service\interfaces\NonPublishPostServiceInterface;
use NastyaKuznet\Blog\Service\interfaces\TagServiceInterface;

class NonPublishPostService implements NonPublishPostServiceInterface
{
    private DatabaseServiceInterface $databaseService;
    private TagServiceInterface $tagService;

    public function __construct(DatabaseServiceInterface $databaseService, TagServiceInterface $tagService)
    {
        $this->databaseService = $databaseService;
        $this->tagService = $tagService;
    }

    public function getAll(): array
    {
        $postsFromDb = $this->databaseService->getAllNonPublishPosts();
        $posts = [];
        foreach ($postsFromDb as $postData) {
            $tags = $this->tagService->getByPostId($postData['id']);
            $posts[] = new Post(
                $postData['id'],
                $postData['title'],
                $postData['preview'],
                $postData['content'],
                $postData['author_id'],
                $postData['user_login'],
                $postData['last_editor_id'],
                $postData['last_editor_login'],
                $postData['create_date'],
                $postData['publish_date'],
                $postData['edit_date'],
                null,
                null,
                $tags
            );
        }
        return $posts;
    }

    public function getById(int $id): ?Post
    {
        $postFromDb = $this->databaseService->getNonPublishPostById($id);

        if (!$postFromDb) {
            return null;
        }
        $tags = $this->tagService->getByPostId($postFromDb['post']['id']);
        try {
            return new Post(
                $postFromDb['post']['id'],
                $postFromDb['post']['title'],
                $postFromDb['post']['preview'],
                $postFromDb['post']['content'],
                $postFromDb['post']['author_id'],
                $postFromDb['author_login'],
                $postFromDb['post']['last_editor_id'],
                $postFromDb['last_editor_login'],
                $postFromDb['post']['create_date'],
                $postFromDb['post']['publish_date'],
                $postFromDb['post']['edit_date'],
                $postFromDb['category_id'],
                $postFromDb['category_name'],
                $tags
            );
        } catch (\Exception $e) {
            error_log("Ошибка при создании объекта Post: " . $e->getMessage());
            return null;
        }
    }
}