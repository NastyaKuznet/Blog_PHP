<?php

namespace NastyaKuznet\Blog\Model;

class Category
{
    public $id;
    public $name;
    public $parentId;
    public $createdAt;

    public function __construct(int $id, string $name, ?int $parentId = null, string $createdAt)
    {
        $this->id = $id;
        $this->name = $name;
        $this->parentId = $parentId;
        $this->createdAt = $createdAt;
    }
}