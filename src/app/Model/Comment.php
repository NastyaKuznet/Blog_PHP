<?php

namespace NastyaKuznet\Blog\Model;

class Comment
{
    public $id;
    public $content;
    public $postId;
    public $userId;

    public function __construct(int $id, string $content, int $postId, int $userId)
    {
        $this->id = $id;
        $this->content = $content;
        $this->postId = $postId;
        $this->userId = $userId;
    }
}
