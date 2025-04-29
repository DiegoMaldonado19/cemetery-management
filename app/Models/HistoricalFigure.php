<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoricalFigure extends Model
{
    use HasFactory;
    
    protected $table = 'historical_figures';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'cui',
        'historical_first_name',
        'historical_last_name',
        'historical_reason',
        'declaration_date',
    ];

    protected $casts = [
        'declaration_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class, 'cui', 'cui');
    }

    public function niches()
    {
        return $this->hasMany(Niche::class, 'historical_figure_id');
    }
}