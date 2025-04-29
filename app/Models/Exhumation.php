<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exhumation extends Model
{
    use HasFactory;
    
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'contract_id',
        'requester_cui',
        'request_date',
        'exhumation_date',
        'reason',
        'agreement_file_path',
        'exhumation_status_id',
        'user_id',
        'notes',
    ];

    protected $casts = [
        'request_date' => 'date',
        'exhumation_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function requester()
    {
        return $this->belongsTo(Person::class, 'requester_cui', 'cui');
    }

    public function status()
    {
        return $this->belongsTo(ExhumationStatus::class, 'exhumation_status_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}