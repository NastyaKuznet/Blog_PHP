<?php

namespace NastyaKuznet\Blog\Model;

class Comment
{
    public function __construct(
        public int $id, 
        public string $content, 
        public int $postId, 
        public int $userId, 
        public string $userLogin, 
        public string $createdDate, 
        public ?string $editDate = null,
        public ?string $deleteDate = null,
        public bool $isEdit = false,
        public bool $isDelete = false
    ) {
    }
}
