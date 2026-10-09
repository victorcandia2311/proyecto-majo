<?php

namespace App\Models;

use Database\Factories\DetalleVentaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleVenta extends Model
{
    /** @use HasFactory<DetalleVentaFactory> */
    use HasFactory;

    protected $table = 'detalle_venta';

    protected $primaryKey = 'id_detalle';

    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'id_venta',
        'id_producto',
        'cantidad',
        'precio_unitario',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'id_venta' => 'integer',
            'id_producto' => 'integer',
            'cantidad' => 'integer',
            'precio_unitario' => 'decimal:2',
        ];
    }

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'id_venta', 'id_venta');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto')->withTrashed();
    }
}
