<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    //


    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id', 'created_at', 'updated_at',
    ];


}
