<?php

namespace DeveoDK\Core\Manager\Databases;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Marquine\EloquentUuid\Uuid;

class Entity extends Model
{
    use SoftDeletes, Uuid;

    /** @var bool */
    protected $incrementing = false;

    /** @var string */
    protected $keyType = 'string';

    /** @var array  */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /** @var array */
    protected $guarded = [];
}
