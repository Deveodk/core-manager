<?php

namespace DeveoDK\Core\Manager\Databases;

use Illuminate\Database\Eloquent\Model;

class Entity extends Model
{
    /**
     * Array of dates
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * Array of guarded attributes for mass assignment
     * @var array
     */
    protected $guarded = [];
}
