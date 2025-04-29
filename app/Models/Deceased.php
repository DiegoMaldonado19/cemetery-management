<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deceased extends Model
{
    use HasFactory;
    
    protected $primaryKey = 'id';
    protected $table = 'deceased';
    
    protected $fillable = [
        'cui',
        'death_date',
        'death_cause_id',
        'origin',
        'notes',
    ];

    protected $casts = [
        'death_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class, 'cui', 'cui');
    }

    public function deathCause()
    {
        return $this->belongsTo(DeathCause::class, 'death_cause_id');
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }
}