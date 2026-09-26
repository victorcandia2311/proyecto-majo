<?php

namespace App\Models;

use Database\Factories\EmpleadoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Empleado extends Authenticatable
{
    /** @use HasFactory<EmpleadoFactory> */
    use HasFactory;

    protected $table = 'empleado';

    protected $primaryKey = 'id_empleado';

    public $timestamps = false;

    /** @var list<string> */
    protected $hidden = ['password', 'remember_token', 'codigo_verificacion', 'codigo_expira_en'];

    /** @var list<string> */
    protected $fillable = [
        'nombres',
        'documento_identidad',
        'usuario',
        'es_eventual',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'es_eventual' => 'boolean',
            'password' => 'hashed',
            'codigo_expira_en' => 'datetime',
        ];
    }

    public function asistencias(): HasMany
    {
        return $this->hasMany(Asistencia::class, 'id_empleado', 'id_empleado');
    }

    public function isAdmin(): bool
    {
        return $this->rol === 'admin';
    }

    public function homeRoute(): string
    {
        return $this->isAdmin() ? 'dashboard.index' : 'ventas.index';
    }
}
