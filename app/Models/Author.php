<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Author extends Model
{
    use HasFactory;

    protected $fillable = [
        '_id',
        'name',
        'slug',
        'image',
        'bio',
    ];

    protected $appends = ['slug'];

    protected function slug(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => is_array($value) ? $value : ['current' => $this->attributes['slug'] ?? '']
        );
    }

    public function blogs(): HasMany
    {
        return $this->hasMany(Blog::class);
    }
}
