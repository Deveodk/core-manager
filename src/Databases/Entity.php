<?php

namespace DeveoDK\Core\Manager\Databases;

use Illuminate\Database\Eloquent\Model;

class Entity extends Model
{
    /** @var string */
    protected $keyType = 'string';

    /** @var array  */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /** @var array */
    protected $guarded = [];
}
