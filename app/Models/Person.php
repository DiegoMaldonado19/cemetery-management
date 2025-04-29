<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    use HasFactory;
    
    protected $table = 'people';
    protected $primaryKey = 'cui';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cui',
        'first_name',
        'last_name',
        'gender_id',
        'email',
        'phone',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function gender()
    {
        return $this->belongsTo(Gender::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class, 'cui', 'cui');
    }

    public function primaryAddress()
    {
        return $this->hasOne(Address::class, 'cui', 'cui')
            ->where('is_primary', true);
    }

    public function deceased()
    {
        return $this->hasOne(Deceased::class, 'cui', 'cui');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'cui', 'cui');
    }

    public function responsibleContracts()
    {
        return $this->hasMany(Contract::class, 'responsible_cui', 'cui');
    }

    public function exhumationRequests()
    {
        return $this->hasMany(Exhumation::class, 'requester_cui', 'cui');
    }

    public function historicalFigure()
    {
        return $this->hasOne(HistoricalFigure::class, 'cui', 'cui');
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}