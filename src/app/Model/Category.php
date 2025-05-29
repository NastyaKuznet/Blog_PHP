<?php

namespace NastyaKuznet\Blog\Model;

class Category
{
    public function __construct(
        public int $id,
        public string $name,
        public string $createdAt,
        public ?int $parentId = null,
    ) {
    }
}