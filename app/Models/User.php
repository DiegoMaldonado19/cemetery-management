<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $primaryKey = 'id';
    
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'cui',
        'role_id',
        'is_active',
        'remember_token',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class, 'cui', 'cui');
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function exhumations()
    {
        return $this->hasMany(Exhumation::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active && $this->role->name !== 'Usuario de Consulta';
    }

    public function isAdmin(): bool
    {
        return $this->role->name === 'Administrador';
    }

    public function isHelper(): bool
    {
        return $this->role->name === 'Ayudante';
    }

    public function isAuditor(): bool
    {
        return $this->role->name === 'Auditor';
    }

    public function isConsultationUser(): bool
    {
        return $this->role->name === 'Usuario de Consulta';
    }
}