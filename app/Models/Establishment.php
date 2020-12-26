<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Establishment extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'state'];

    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'state' => 'bool',
    ];

}
