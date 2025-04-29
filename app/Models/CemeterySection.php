<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CemeterySection extends Model
{
    use HasFactory;
    
    protected $table = 'cemetery_sections';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'name',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function blocks()
    {
        return $this->hasMany(CemeteryBlock::class, 'section_id');
    }
}