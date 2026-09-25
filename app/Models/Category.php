<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        '_id',
        'title',
        'name',
        'slug',
        'description',
        'image',
        'productCount',
    ];

    protected $appends = ['slug'];

    protected function slug(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => is_array($value) ? $value : ['current' => $this->attributes['slug'] ?? '']
        );
    }

    protected function slugText(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['slug'] ?? ''
        );
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }
}
