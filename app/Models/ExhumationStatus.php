<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExhumationStatus extends Model
{
    use HasFactory;
    
    protected $table = 'exhumation_statuses';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'name',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function exhumations()
    {
        return $this->hasMany(Exhumation::class, 'exhumation_status_id');
    }
}