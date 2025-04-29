<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChangeLog extends Model
{
    use HasFactory;
    
    protected $table = 'change_logs';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'table_name',
        'record_id',
        'changed_field',
        'old_value',
        'new_value',
        'changed_at',
        'user_id',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}