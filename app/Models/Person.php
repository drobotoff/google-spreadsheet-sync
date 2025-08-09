<?php

namespace App\Models;

use Database\Factories\PersonFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\StatusEnum;

class Person extends Model
{
    /** @use HasFactory<PersonFactory> */
    use HasFactory;

    protected $table = 'persons';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'serial_passport',
        'number_passport',
        'status'
    ];

    protected $casts = [
        'status' => StatusEnum::class,
    ];

    /**
     * Local scope для Allowed
     * @param Builder $query
     * @return Builder
     */
    public function scopeAllowed(Builder $query): Builder
    {
        return $query->where("status", StatusEnum::ALLOWED);
    }
}
