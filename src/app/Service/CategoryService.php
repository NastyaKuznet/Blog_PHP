<?php

namespace NastyaKuznet\Blog\Service;

use NastyaKuznet\Blog\Model\Category;
use NastyaKuznet\Blog\Service\DatabaseService;

class CategoryService
{
    private DatabaseService $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    public function getAllCategories(): array
    {
        return $this->databaseService->getAllCategories();
    }

    public function getCategoriesTree(): array
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

    public function getCategoryById(int $categoryId): ?Category
    {
        return $this->databaseService->getCategoryById($categoryId);
    }

    public function addCategory(string $name, ?int $parentId): bool
    {
        return $this->databaseService->addCategory($name, $parentId);
    }

    public function getCategoriesByPostId(int $postId): array
    {
        return $this->databaseService->getCategoriesByPostId($postId);
    }

    public function getPostsByCategoryId(int $categoryId): array
    {
        return $this->databaseService->getPostsByCategoryId($categoryId);
    }

    public function deleteCategory(int $categoryId): bool
    {
        return $this->databaseService->deleteCategory($categoryId);
    }
}