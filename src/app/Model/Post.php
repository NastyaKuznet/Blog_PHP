<?php

namespace NastyaKuznet\Blog\Model;

class Post
{
    public function __construct(
        public int $id, 
        public string $title, 
        public string $preview, 
        public string $content, 
        public int $userId, 
        public string $userLogin, 
        public int $lastEditorId, 
        public string $lastEditorLogin, 
        public string $createDate, 
        public ?string $publishDate = null, 
        public ?string $editDate = null, 
        public ?int $categoryId = null, 
        public ?string $categoryName = null, 
        public array $tags = [], 
        public int $likes = 0, 
        public int $commentCount = 0
        ) {
    }
}
