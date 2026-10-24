<?php

namespace App\Models;

use Database\Factories\ProductoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    /** @use HasFactory<ProductoFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'producto';

    protected $primaryKey = 'id_producto';

    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'nombre_producto',
        'descripcion',
        'imagen_path',
        'stock_actual',
        'stock_minimo_alerta',
        'precio_venta',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'stock_actual' => 'integer',
            'stock_minimo_alerta' => 'integer',
            'precio_venta' => 'decimal:2',
        ];
    }

    public function movimientosInventario(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'id_producto', 'id_producto');
    }

    public function detallesVenta(): HasMany
    {
        return $this->hasMany(DetalleVenta::class, 'id_producto', 'id_producto');
    }

    public function productosProveedores(): HasMany
    {
        return $this->hasMany(ProductoProveedor::class, 'id_producto', 'id_producto');
    }

    public function proveedores(): BelongsToMany
    {
        return $this->belongsToMany(Proveedor::class, 'producto_proveedor', 'id_producto', 'id_proveedor', 'id_producto', 'id_proveedor')
            ->using(ProductoProveedor::class)
            ->withPivot('precio_compra');
    }

    public function ventas(): BelongsToMany
    {
        return $this->belongsToMany(Venta::class, 'detalle_venta', 'id_producto', 'id_venta', 'id_producto', 'id_venta')
            ->withPivot('id_detalle', 'cantidad', 'precio_unitario');
    }
}
