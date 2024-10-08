<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function Product(){
        return $this->belongsTo(Product::class, 'PostId');
    }

    public function User(){
        return $this->belongsTo(User::class, 'UserId');
    }
}
