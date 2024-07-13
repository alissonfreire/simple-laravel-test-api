<?php

namespace App\Interfaces;

interface TodoRepositoryInterface
{
    public function all(array $filters);

    public function create(array $details);

    public function findById(int $id);

    public function delete(int $id);

    public function done(int $id);

    public function undone(int $id);
}
