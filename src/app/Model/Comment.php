<?php

namespace NastyaKuznet\Blog\Model;

class Comment
{
    public $id;
    public $content;
    public $postId;
    public $userId;
    public $userNickname;
    public $createdDate;
    public $editDate;
    public $deleteDate;
    public $isEdit;
    public $isDelete;

    public function __construct(
        int $id, 
        string $content, 
        int $postId, 
        int $userId, 
        string $userNickname, 
        string $createdDate, 
        ?string $editDate = null,
        ?string $deleteDate = null,
        bool $isEdit = false,
        bool $isDelete = false)
    {
        $this->id = $id;
        $this->content = $content;
        $this->postId = $postId;
        $this->userId = $userId;
        $this->userNickname = $userNickname;
        $this->createdDate = $createdDate;
        $this->editDate = $editDate;
        $this->deleteDate = $deleteDate;
        $this->isEdit = $isEdit;
        $this->isDelete = $isDelete;
    }
}
