<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'contract_id',
        'receipt_number',
        'amount',
        'issue_date',
        'payment_date',
        'payment_status_id',
        'receipt_file_path',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function status()
    {
        return $this->belongsTo(PaymentStatus::class, 'payment_status_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}