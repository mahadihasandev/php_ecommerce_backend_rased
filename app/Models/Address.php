<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        '_id',
        'user_id',
        'name',
        'email',
        'address',
        'city',
        'District',
        'state',
        'zip',
        'phone',
        'default',
    ];

    protected $casts = [
        'default' => 'boolean',
    ];
}
