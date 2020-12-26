<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Scan extends Model
{
    use HasFactory;



    protected $hidden = [
        "created_at",
        "updated_at",
        "user_number",
        "resto_id",
        "vigilant_id"
    ];
}
