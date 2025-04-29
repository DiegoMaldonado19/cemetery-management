<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CemeteryAvenue extends Model
{
    use HasFactory;
    
    protected $table = 'cemetery_avenues';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'block_id',
        'name',
        'avenue_number',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function block()
    {
        return $this->belongsTo(CemeteryBlock::class, 'block_id');
    }

    public function niches()
    {
        return $this->hasMany(Niche::class, 'avenue_id');
    }
}