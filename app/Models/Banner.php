<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        '_id',
        'user_id',
        'title',
        'subtitle',
        'description',
        'image',
        'badge',
        'discountAmount',
        'link',
    ];

    protected $appends = ['productSlug'];

    public function getProductSlugAttribute(): array
    {
        if (!empty($this->link)) {
            $slug = preg_replace('#^/?product/#', '', trim($this->link));
            return [$slug];
        }
        return ['sony-wh-1000xm5-wireless-headphones'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
