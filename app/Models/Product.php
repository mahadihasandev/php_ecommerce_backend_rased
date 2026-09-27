<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        '_id',
        'user_id',
        'name',
        'slug',
        'price',
        'discount',
        'stock',
        'status',
        'variant',
        'isFeatured',
        'brand_id',
        'images',
        'keyfeature',
        'description',
    ];

    protected $casts = [
        'images' => 'array',
        'description' => 'array',
        'price' => 'float',
        'discount' => 'float',
        'stock' => 'integer',
        'isFeatured' => 'boolean',
    ];

    protected $appends = ['slug'];

    protected function keyfeature(): Attribute
    {
        $cleanList = function ($items) {
            if (! is_array($items)) {
                return [];
            }
            $result = [];
            foreach ($items as $item) {
                if (! is_string($item)) {
                    continue;
                }
                $trimmed = trim($item, " \t\n\r\0\x0B\"'•-");
                if ($trimmed !== '') {
                    $result[] = $trimmed;
                }
            }

            return array_values($result);
        };

        return Attribute::make(
            get: function ($value) use ($cleanList) {
                if (empty($value)) {
                    return [];
                }
                if (is_array($value)) {
                    return $cleanList($value);
                }
                $decoded = json_decode($value, true);
                if (is_array($decoded)) {
                    return $cleanList($decoded);
                }
                if (is_string($value)) {
                    $unquoted = trim($value, " \t\n\r\0\x0B\"'");
                    $parts = preg_split('/(?<=[.!?])\s+|\r?\n|•\s*/', $unquoted, -1, PREG_SPLIT_NO_EMPTY);

                    return $cleanList($parts ?: [$unquoted]);
                }

                return (array) $value;
            },
            set: function ($value) use ($cleanList) {
                if (is_array($value)) {
                    return json_encode($cleanList($value));
                }
                if (is_string($value)) {
                    $decoded = json_decode($value, true);
                    if (is_array($decoded)) {
                        return json_encode($cleanList($decoded));
                    }
                    $unquoted = trim($value, " \t\n\r\0\x0B\"'");
                    $parts = preg_split('/(?<=[.!?])\s+|\r?\n|•\s*/', $unquoted, -1, PREG_SPLIT_NO_EMPTY);

                    return json_encode($cleanList($parts ?: [$unquoted]));
                }

                return $value;
            }
        );
    }

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

    public function scopeRankedBySales(Builder $query): Builder
    {
        // Aggregate the order history once instead of executing a correlated sum
        // for every product. COALESCE also keeps unsold products last on PostgreSQL.
        $sales = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', '!=', 'cancelled')
            ->select('order_items.product_id')
            ->selectRaw('SUM(order_items.quantity) as sales_count')
            ->groupBy('order_items.product_id');

        if ($query->getQuery()->columns === null) {
            $query->select('products.*');
        }

        return $query->leftJoinSub($sales, 'product_sales', 'products.id', '=', 'product_sales.product_id')
            ->selectRaw('COALESCE(product_sales.sales_count, 0) as sales_count')
            ->orderByDesc('sales_count')
            ->orderBy('products.id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
