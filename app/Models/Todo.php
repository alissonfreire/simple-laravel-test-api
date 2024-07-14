<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Todo',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'user_id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string', example: 'do something tomorrow at 10'),
        new OA\Property(property: 'description', type: 'string', example: 'remember to do something tomorrow at 10 am'),
        new OA\Property(property: 'done', type: 'bool', example: false),
        new OA\Property(property: 'done_at', type: 'string', format: 'date-time', example: null),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2024-07-13T02:01:20.000000Z'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2024-07-13T02:01:20.000000Z'),
    ]
)]
class Todo extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'user_id',
        'done',
        'done_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'done' => 'bool',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function boot()
    {
        parent::boot();

        self::creating(function (self $todo) {
            $todo->done = false;
            $todo->done_at = null;
        });
    }
}
