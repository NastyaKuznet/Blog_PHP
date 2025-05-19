<?php

namespace NastyaKuznet\Blog\Model;

class Post
{
    public $id;
    public $title;
    public $preview;
    public $content;
    public $userId;
    public $userNickname;
    public $lastEditorId;
    public $lastEditorNickname;
    public $commentCount;
    public $createDate;
    public $publishDate;
    public $editDate;
    public $likes;

    public function __construct(int $id, string $title, string $preview, string $content, int $userId, string $userNickname, int $lastEditorId, string $lastEditorNickname, string $createDate, ?string $publishDate, ?string $editDate, int $likes = 0, int $commentCount = 0)
    {
        $this->id = $id;
        $this->title = $title;
        $this->preview = $preview;
        $this->content = $content;
        $this->userId = $userId;
        $this->userNickname = $userNickname;
        $this->lastEditorId = $lastEditorId;
        $this->lastEditorNickname = $lastEditorNickname;
        $this->commentCount = $commentCount;
        $this->createDate = $createDate;
        $this->publishDate = $publishDate;
        $this->editDate = $editDate;
        $this->likes = $likes;
    }
}
