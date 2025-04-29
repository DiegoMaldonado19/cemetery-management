<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NicheType extends Model
{
    use HasFactory;
    
    protected $table = 'niche_types';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'name',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function niches()
    {
        return $this->hasMany(Niche::class, 'niche_type_id');
    }
}