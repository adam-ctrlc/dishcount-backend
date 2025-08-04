<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'category_id',
        'product_name',
        'product_description',
        'price',
        'image',
        'stock',
        'discount',
        'is_active',
    ];

    protected $hidden = [];

    protected $casts = [
        'price' => 'decimal:2',
        'discount' => 'decimal:1',
        'stock' => 'integer',
        'is_active' => 'boolean',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}
