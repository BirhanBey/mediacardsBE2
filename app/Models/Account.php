<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Account extends Model
{
    protected $fillable = ['userName', 'email', 'password', 'img', 'description', 'url'];
    public function urls()
    {
        return $this->hasMany(Url::class);
    }
}

// app/Models/Url.php
class Url extends Model
{
    protected $fillable = ['name', 'link', 'isActive', 'account_id'];
    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
