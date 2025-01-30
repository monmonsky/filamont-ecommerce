<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    use HasFactory;

    protected $fillable = ['name'];


    public function AttributeValue()
    {
        return $this->hasMany(AttributeValue::class);
    }

    public function values()
    {
        return $this->hasMany(AttributeValue::class, 'attribute_id');
    }
        
}
