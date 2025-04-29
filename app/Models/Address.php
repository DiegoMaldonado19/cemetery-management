<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    
    protected $fillable = [
        'cui',
        'department_id',
        'address_line',
        'reference',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class, 'cui', 'cui');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}