<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmpleadoTemporal extends Model
{
    protected $table = 'empleado_temporal';

    protected $primaryKey = 'id_temporal';

    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'id_empleado',
        'nombre_temp',
        'fecha_inicio',
        'hora_ingreso',
        'hora_salida',
        'agencia_outsourcing',
    ];

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'id_empleado', 'id_empleado');
    }
}
