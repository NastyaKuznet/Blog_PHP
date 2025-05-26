<?php

namespace NastyaKuznet\Blog\Model;

class Post
{
    public $id;
    public $title;
    public $preview;
    public $content;
    public $userId;
    public $userLogin;
    public $lastEditorId;
    public $lastEditorLogin;
    public $commentCount;
    public $createDate;
    public $publishDate;
    public $categoryId;
    public $categoryName;
    public $tags;
    public $editDate;
    public $likes;

    public function __construct(int $id, string $title, string $preview, string $content, int $userId, string $userLogin, int $lastEditorId, string $lastEditorLogin, string $createDate, ?string $publishDate, ?string $editDate, ?int $categoryId, ?string $categoryName, array $tags = [], int $likes = 0, int $commentCount = 0)
    {
        $this->id = $id;
        $this->title = $title;
        $this->preview = $preview;
        $this->content = $content;
        $this->userId = $userId;
        $this->userLogin = $userLogin;
        $this->lastEditorId = $lastEditorId;
        $this->lastEditorLogin = $lastEditorLogin;
        $this->commentCount = $commentCount;
        $this->createDate = $createDate;
        $this->publishDate = $publishDate;
        $this->editDate = $editDate;
        $this->categoryId = $categoryId;
        $this->categoryName = $categoryName;
        $this->tags = $tags;
        $this->likes = $likes;
    }
}
