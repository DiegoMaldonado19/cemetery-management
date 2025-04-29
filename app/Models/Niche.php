<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Niche extends Model
{
    use HasFactory;
    
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'code',
        'street_id',
        'avenue_id',
        'location_reference',
        'niche_type_id',
        'niche_status_id',
        'historical_figure_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function street()
    {
        return $this->belongsTo(CemeteryStreet::class);
    }

    public function avenue()
    {
        return $this->belongsTo(CemeteryAvenue::class);
    }

    public function type()
    {
        return $this->belongsTo(NicheType::class, 'niche_type_id');
    }

    public function status()
    {
        return $this->belongsTo(NicheStatus::class, 'niche_status_id');
    }

    public function historicalFigure()
    {
        return $this->belongsTo(HistoricalFigure::class);
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    public function currentContract()
    {
        return $this->hasOne(Contract::class)
            ->whereHas('status', function ($query) {
                $query->whereIn('name', ['Vigente', 'En Gracia']);
            })
            ->latest('start_date');
    }
}