<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    protected $table = 'people';
    
    protected $primaryKey = 'cui';
    
    public $incrementing = false;
    
    protected $keyType = 'string';
    
    protected $fillable = [
        'cui',
        'first_name',
        'last_name',
        'gender_id',
        'email',
        'phone'
    ];
}