<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = 
    [
        't_name',
        's_name',
        'category',
        'quantity',
        'company_name',
        'exp_date',
        'price',
        'image',
];
public function users()
{
    return $this->belongsToMany(User::class);
}
public function orders()
{
    return $this->hasMany(Order::class);
}
}
