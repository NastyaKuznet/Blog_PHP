<?php

namespace NastyaKuznet\Blog\Service;

use NastyaKuznet\Blog\Model\Tag;
use NastyaKuznet\Blog\Service\interfaces\DatabaseServiceInterface;
use NastyaKuznet\Blog\Service\interfaces\TagServiceInterface;

class TagService implements TagServiceInterface
{
    private DatabaseServiceInterface $databaseService;

    public function __construct(DatabaseServiceInterface $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    // Метод для получения тегов поста
    public function getByPostId(int $postId): array
    {
        $tagsFromDb = $this->databaseService->getTagsByPostId($postId);
        $tags = [];
        foreach ($tagsFromDb as $tagData) {
            $tags[] = new Tag(
                $tagData['id'],
                $tagData['name']
            );
        }
        return $tags;
    }

    // Метод для получения всех тегов
    public function getAll(): array
    {
        $tagsFromDb = $this->databaseService->getAllTags();
        $tags = [];
        foreach ($tagsFromDb as $tagData) {
            $tags[] = [
                'id' => $tagData['id'],
                'name' => $tagData['name']
            ];
        }
        return $tags;
    }
}