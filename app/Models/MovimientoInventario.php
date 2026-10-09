<?php

namespace App\Models;

use Database\Factories\MovimientoInventarioFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoInventario extends Model
{
    /** @use HasFactory<MovimientoInventarioFactory> */
    use HasFactory;

    protected $table = 'movimiento_inventario';

    protected $primaryKey = 'id_movimiento';

    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'id_producto',
        'tipo_movimiento',
        'cantidad',
        'motivo',
        'fecha_hora',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'id_producto' => 'integer',
            'cantidad' => 'integer',
            'fecha_hora' => 'datetime',
        ];
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto')->withTrashed();
    }
}
