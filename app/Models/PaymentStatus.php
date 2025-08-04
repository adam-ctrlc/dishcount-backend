<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentStatus extends Model
{
    protected $fillable = [
        'status',
    ];

    protected $hidden = [];

    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'payment_status_id', 'id');
    }
}
