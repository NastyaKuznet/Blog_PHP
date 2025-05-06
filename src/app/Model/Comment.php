<?php

namespace NastyaKuznet\Blog\Model;

class Comment
{
    public $id;
    public $content;
    public $postId;
    public $userId;
    public $userNickname;
    public $createdAt;

    public function __construct(int $id, string $content, int $postId, int $userId, string $userNickname, string $createdAt)
    {
        $this->id = $id;
        $this->content = $content;
        $this->postId = $postId;
        $this->userId = $userId;
        $this->userNickname = $userNickname;
        $this->createdAt = $createdAt;
    }
}
