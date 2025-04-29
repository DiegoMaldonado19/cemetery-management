<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeathCause extends Model
{
    use HasFactory;
    
    protected $table = 'death_causes';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'name',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function deceased()
    {
        return $this->hasMany(Deceased::class, 'death_cause_id');
    }
}