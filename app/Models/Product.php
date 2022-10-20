<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'properties',
        'user_id',
    ];

    protected $casts = [
        'properties' => 'array'
    ];

    public function setPropertiesAttribute($array)
    {
        $this->attributes['properties'] = collect($array)->filter(fn($item) => !is_null($item['key']));
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
