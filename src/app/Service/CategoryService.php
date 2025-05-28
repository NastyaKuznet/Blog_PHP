<?php

namespace NastyaKuznet\Blog\Service;

use NastyaKuznet\Blog\Model\Category;
use NastyaKuznet\Blog\Model\Post;
use NastyaKuznet\Blog\Service\interfaces\CategoryServiceInterface;
use NastyaKuznet\Blog\Service\interfaces\DatabaseServiceInterface;
use NastyaKuznet\Blog\Service\interfaces\TagServiceInterface;

class CategoryService implements CategoryServiceInterface
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
        return $this->databaseService->getAllCategories();
    }

    public function getTree(): array
    {
        $categories = $this->databaseService->getAllCategories();
        $indexed = [];

        foreach ($categories as $category) {
            $indexed[$category['id']] = [
                'id' => (int)$category['id'],
                'name' => $category['name'],
                'parent_id' => $category['parent_id'] === null ? null : (int)$category['parent_id'],
                'created_date' => $category['created_date'],
                'children' => []
            ];
        }

        $tree = [];
        foreach ($indexed as $id => &$category) { 
            if ($category['parent_id'] && isset($indexed[$category['parent_id']])) {
                $indexed[$category['parent_id']]['children'][] = &$category; 
                foreach ($tree as $key => $rootCategory) {
                    if ($rootCategory['id'] === $category['id']) {
                        unset($tree[$key]);
                        break;
                    }
                }


            } else {
                $tree[] = &$category; 
            }
        }

        unset($category); 

        $treeWithoutReferences = json_decode(json_encode($tree), true);


        return $treeWithoutReferences;
    }

    public function getById(int $id): ?Category
    {
        return $this->databaseService->getCategoryById($id);
    }

    public function add(string $name, ?int $parentId): bool
    {
        return $this->databaseService->addCategory($name, $parentId);
    }

    public function getByPostId(int $postId): array
    {
        return $this->databaseService->getCategoriesByPostId($postId);
    }

    public function getPostsByCategoryId(int $categoryId): array
    {
        $postsFromDb = $this->databaseService->getPostsByCategoryId($categoryId);
        $posts = [];
        foreach ($postsFromDb as $postData) {
            $tags = $this->tagService->getByPostId($postData['id']);
            $posts[] = new Post(
                $postData['id'],
                $postData['title'],
                $postData['preview'],
                $postData['content'],
                $postData['author_id'],
                $postData['user_nickname'],
                $postData['last_editor_id'],
                $postData['last_editor_nickname'],
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

    public function delete(int $id): bool
    {
        return $this->databaseService->deleteCategory($id);
    }

    public function connectPostAndCategory(int $postId, int $categoryId): bool
    {
        return $this->databaseService->connectPostAndCategory($postId, $categoryId);
    }
}