<?php

namespace App\Repositories;

use App\Exceptions\Api\NotFoundException;
use App\Interfaces\TodoRepositoryInterface;
use App\Models\Todo;
use Carbon\Carbon;

class TodoRepository implements TodoRepositoryInterface
{
    public function all(array $filters = [])
    {
        return Todo::all();
    }

    public function create(array $details)
    {
        return Todo::create($details);
    }

    public function findById(int $id)
    {
        return Todo::where('id', $id)->first();
    }

    public function delete(int $id)
    {
        $todo = $this->findByIdOrExplode($id);

        return $todo->delete();
    }

    public function done(int $id)
    {
        $this->updateByIdOrExplode($id, [
            'done' => true,
            'done_at' => Carbon::now(),
        ]);

        return true;
    }

    public function undone(int $id)
    {
        $this->updateByIdOrExplode($id, [
            'done' => false,
            'done_at' => null,
        ]);

        return true;
    }

    private function updateByIdOrExplode($id, $details): Todo
    {
        $todo = $this->findByIdOrExplode($id);

        $todo->update($details);

        return $todo;
    }

    private function findByIdOrExplode($id): Todo
    {
        $todo = $this->findById($id);

        if (! $todo) {
            throw new NotFoundException();
        }

        return $todo;
    }
}
