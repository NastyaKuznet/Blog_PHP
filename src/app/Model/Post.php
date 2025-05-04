<?php

namespace NastyaKuznet\Blog\Model;

class Post
{
    public $id;
    public $title;
    public $content;
    public $likes;
    public $userId;
    public $commentCount;

    public function __construct(int $id, string $title, string $content, int $likes, int $userId, int $commentCount = 0)
    {
        $this->id = $id;
        $this->title = $title;
        $this->content = $content;
        $this->likes = $likes;
        $this->userId = $userId;
        $this->commentCount = $commentCount;
    }
}
