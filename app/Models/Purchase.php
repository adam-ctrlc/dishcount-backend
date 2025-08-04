<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Product;
use App\Models\Shop;
use App\Models\PaymentStatus;
use App\Models\PaymentMethod;

class Purchase extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    protected $fillable = [
        'id',
        'payment_status_id',
        'payment_method_id',
        'user_id',
        'product_id',
        'quantity',
        'total_price',
        'reference_number',
        'receipt',
        'is_active',
        'refund_reason',
        'received_at',
    ];

    protected $hidden = [];

    protected $casts = [
        'is_active' => 'boolean',
        'total_price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function shop()
    {
        return $this->hasOneThrough(Shop::class, Product::class, 'id', 'id', 'product_id', 'shop_id');
    }

    public function paymentStatus()
    {
        return $this->belongsTo(PaymentStatus::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
