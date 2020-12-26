<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class Vigilant extends Authenticatable
{
    use HasApiTokens, Notifiable, HasFactory;

    protected $fillable = ['first_name', 'last_name'];

    protected $hidden = ['created_at', 'updated_at', 'password', 'code'];

    public function findForPassport($username)
    {
        return $this->where('code', $username)->first();
    }
}
