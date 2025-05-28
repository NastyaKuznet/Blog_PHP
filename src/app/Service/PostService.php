<?php

namespace NastyaKuznet\Blog\Service;

use NastyaKuznet\Blog\Service\interfaces\DatabaseServiceInterface;
use NastyaKuznet\Blog\Model\Post;
use NastyaKuznet\Blog\Service\interfaces\PostServiceInterface;
use NastyaKuznet\Blog\Service\interfaces\TagServiceInterface;

class PostService implements PostServiceInterface
{
    private DatabaseServiceInterface $databaseService;
    private TagServiceInterface $tagService;

    public function __construct(DatabaseServiceInterface $databaseService, TagServiceInterface $tagService)
    {
        $this->databaseService = $databaseService;
        $this->tagService = $tagService;
    }

    private function getPostsWithFilters(?string $sortBy, ?string $order, ?string $authorLogin, ?string $tag): array
    {
        if ($authorLogin) {
            $postsFromDb = $this->databaseService->getPostsByAuthor($authorLogin);
            return $postsFromDb;
        }

        if ($tag) {
            $postsFromDb = $this->databaseService->getPostsByTag($tag);
            return $postsFromDb;
        }

        switch ($sortBy) {
            case 'author':
                if($order === 'asc'){
                    $postsFromDb = $this->databaseService->getPostsByAuthorAlphabetical();
                    return $postsFromDb;
                } else {
                    $postsFromDb = $this->databaseService->getPostsByAuthorReverseAlphabetical();
                    return $postsFromDb;
                }
                break;
            case 'likes':
                if($order === 'asc'){
                    $postsFromDb = $this->databaseService->getPostsByLikesAscending();
                    return $postsFromDb;
                } else {
                    $postsFromDb = $this->databaseService->getPostsByLikesDescending();
                    return $postsFromDb;
                }
                break;
            case 'comments':
                if($order === 'asc'){
                    $postsFromDb = $this->databaseService->getPostsByCommentsAscending();
                    return $postsFromDb;
                } else {
                    $postsFromDb = $this->databaseService->getPostsByCommentsDescending();
                    return $postsFromDb;
                }
                break;
        }
        $postsFromDb = $this->databaseService->getAllPosts();
        return $postsFromDb;
    }

    public function getAll(?string $sortBy, ?string $order, ?string $authorNickname, ?string $tag): array
    {
        $postsFromDb = $this->getPostsWithFilters($sortBy, $order, $authorLogin, $tag);
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
                $postData['category_id'],
                $postData['category_name'],
                $tags,
                $postData['like_count'],
                $postData['comment_count']
            );
        }
        
        return $posts;
    }

    public function getById(int $id): ?Post
    {
        $postFromDb = $this->databaseService->getPostById($id);

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
                $tags,
                $postFromDb['like_count'],
                $postFromDb['comment_count']
            );
        } catch (\Exception $e) {
            error_log("Ошибка при создании объекта Post: " . $e->getMessage());
            return null;
        }
    }

    public function edit(int $id, string $title, string $preview, string $content, int $userId, array $tags): bool 
    {
        $oldTagsFromDB = $this->tagService->getByPostId($id);
        $oldTags = [];
        foreach($oldTagsFromDB as $oldTag)
        {
            $oldTags[] = $oldTag->name;
        }

        $tagsForDelete = array_diff($oldTags, $tags);
        foreach($tagsForDelete as $tagDel)
        {
            $isSuccess = $this->databaseService->deleteTag($tagDel, $id);
            if(!$isSuccess)
            {
                echo('Не удалось удалить тег: ');
                echo($tagDel);
            }
        }

        $tagsForInsert = array_diff($tags, $oldTags);
        foreach($tagsForInsert as $tagIns)
        {
            $isSuccess = $this->databaseService->addTag($tagIns, $id);
            if(!$isSuccess)
            {
                echo('Не удалось вставить тег: ');
                echo($tagDel);
            }
        }

        return $this->databaseService->editPost($id, $title,  $preview, $content, $userId); 
    }

    public function publish(int $id): bool 
    {
        return $this->databaseService->publishPost($id); 
    }

    public function delete(int $id): bool 
    {
        return $this->databaseService->deletePost($id); 
    }

    public function getCountPostsByUserId(int $userId): int 
    {
        return $this->databaseService->getCountPostsByUserId($userId);
    }

    public function getByUserId(int $userId): array 
    {
        $postsFromDb = $this->databaseService->getPostsByUserId($userId);
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
                $postData['category_id'],
                $postData['category_name'],
                $tags,
                $postData['like_count'],
                $postData['comment_count']
            );
        }
        return $posts;
    }

    public function add(string $title, string $preview, string $content, int $userId, array $tags): bool
    {
        $postId = $this->databaseService->addPostAndGetId($title, $preview, $content, $userId);
        if (!$postId) return false;

        foreach ($tags as $tagName) {
            $tagSaved = $this->databaseService->addTag($tagName, $postId);

            if (!$tagSaved)
            {
                echo ('тег не сохранен: ');
                echo ($tagName);
            }
        }
        return true;
    }
}
