<?php

namespace App\Services;

use App\Interfaces\TodoRepositoryInterface;
use App\Models\User;

class TodoService
{
    protected User $user;

    public function __construct(protected TodoRepositoryInterface $todoRepository)
    {
        $this->user = auth()->user();
    }

    public function all(array $filters = [])
    {
        $filters = $this->ensureUserId($filters);

        return $this->todoRepository->all($filters);
    }

    public function create(array $details)
    {
        $details = $this->ensureUserId($details);

        return $this->todoRepository->create($details);
    }

    public function findById(int $id)
    {
        return $this->todoRepository->findById($id);
    }

    public function delete(int $id)
    {
        return $this->todoRepository->delete($id);
    }

    public function done(int $id)
    {
        return $this->todoRepository->done($id);
    }

    public function undone(int $id)
    {
        return $this->todoRepository->undone($id);
    }

    private function ensureUserId(array $data)
    {
        if (! in_array('user_id', $data)) {
            $data['user_id'] = $this->user->id;
        }

        return $data;
    }
}
