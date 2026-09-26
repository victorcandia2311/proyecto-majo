<?php

namespace App\Models;

use Database\Factories\VentaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venta extends Model
{
    /** @use HasFactory<VentaFactory> */
    use HasFactory;

    protected $table = 'venta';

    protected $primaryKey = 'id_venta';

    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'fecha_hora',
        'cliente_nombre',
        'cliente_documento',
        'metodo_pago',
        'total_venta',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'fecha_hora' => 'datetime',
            'total_venta' => 'decimal:2',
        ];
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleVenta::class, 'id_venta', 'id_venta');
    }

    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'detalle_venta', 'id_venta', 'id_producto', 'id_venta', 'id_producto')
            ->withPivot('id_detalle', 'cantidad', 'precio_unitario');
    }
}
