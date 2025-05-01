<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Log;

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
        // Log para depuración
        Log::info('Verificando acceso a panel', [
            'usuario' => $this->email,
            'activo' => $this->is_active,
            'rol' => $this->role ? $this->role->name : 'sin rol',
            'panel' => $panel->getId()
        ]);

        // Si no está activo, no puede acceder a ningún panel
        if (!$this->is_active) {
            Log::warning('Usuario inactivo: ' . $this->email);
            return false;
        }

        // Si no tiene rol asignado, no puede acceder
        if (!$this->role) {
            Log::warning('Usuario sin rol: ' . $this->email);
            return false;
        }

        $roleName = $this->role->name;

        // Verificar según el panel solicitado
        if ($panel->getId() === 'admin') {
            // Solo admin, ayudante y auditor pueden acceder al panel admin
            $canAccess = in_array($roleName, ['Administrador', 'Ayudante', 'Auditor']);
            Log::info('Acceso a panel admin: ' . ($canAccess ? 'permitido' : 'denegado'));
            return $canAccess;
        }

        if ($panel->getId() === 'consultation') {
            // Solo usuario de consulta puede acceder al panel de consulta
            $canAccess = $roleName === 'Usuario de Consulta';
            Log::info('Acceso a panel consultation: ' . ($canAccess ? 'permitido' : 'denegado'));
            return $canAccess;
        }

        // Para cualquier otro panel, denegar acceso por defecto
        Log::info('Panel desconocido: ' . $panel->getId() . ', acceso denegado');
        return false;
    }

    public function isAdmin(): bool
    {
        return $this->role && $this->role->name === 'Administrador';
    }

    public function isHelper(): bool
    {
        return $this->role && $this->role->name === 'Ayudante';
    }

    public function isAuditor(): bool
    {
        return $this->role && $this->role->name === 'Auditor';
    }

    public function isConsultationUser(): bool
    {
        return $this->role && $this->role->name === 'Usuario de Consulta';
    }
}
