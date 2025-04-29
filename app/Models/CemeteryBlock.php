<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CemeteryBlock extends Model
{
    use HasFactory;
    
    protected $table = 'cemetery_blocks';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'section_id',
        'name',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function section()
    {
        return $this->belongsTo(CemeterySection::class);
    }

    public function streets()
    {
        return $this->hasMany(CemeteryStreet::class, 'block_id');
    }

    public function avenues()
    {
        return $this->hasMany(CemeteryAvenue::class, 'block_id');
    }
}