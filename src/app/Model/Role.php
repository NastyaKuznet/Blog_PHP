<?php

namespace NastyaKuznet\Blog\Model;

class Role
{
    public function __construct(
        public int $id, 
        public string $name
    ) {
    }
}