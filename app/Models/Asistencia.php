<?php

namespace App\Models;

use Database\Factories\AsistenciaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asistencia extends Model
{
    /** @use HasFactory<AsistenciaFactory> */
    use HasFactory;

    protected $table = 'asistencia';

    protected $primaryKey = 'id_asistencia';

    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'id_empleado',
        'fecha',
        'hora_ingreso',
        'hora_salida',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'id_empleado' => 'integer',
            'fecha' => 'date',
        ];
    }

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'id_empleado', 'id_empleado');
    }
}
