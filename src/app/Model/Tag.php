<?php

namespace NastyaKuznet\Blog\Model;

class Tag
{
    public function __construct(
        public int $id, 
        public string $name
    ) {
    }
}