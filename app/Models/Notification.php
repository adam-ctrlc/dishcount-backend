<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Notification extends Model
{
  protected $fillable = [
    'user_id',
    'type',
    'data',
    'read_at',
  ];

  protected $casts = [
    'data' => 'array',
    'read_at' => 'datetime',
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }
}
