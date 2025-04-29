<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;
    
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'niche_id',
        'deceased_id',
        'responsible_cui',
        'start_date',
        'end_date',
        'grace_date',
        'contract_status_id',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'grace_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function niche()
    {
        return $this->belongsTo(Niche::class);
    }

    public function deceased()
    {
        return $this->belongsTo(Deceased::class);
    }

    public function responsible()
    {
        return $this->belongsTo(Person::class, 'responsible_cui', 'cui');
    }

    public function status()
    {
        return $this->belongsTo(ContractStatus::class, 'contract_status_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function exhumations()
    {
        return $this->hasMany(Exhumation::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}