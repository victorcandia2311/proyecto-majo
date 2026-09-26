<?php

namespace App\Models;

use Database\Factories\ProductoProveedorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductoProveedor extends Pivot
{
    /** @use HasFactory<ProductoProveedorFactory> */
    use HasFactory;

    protected $table = 'producto_proveedor';

    /**
     * No hay clave simple: Pivot utiliza id_producto e id_proveedor juntos.
     */
    protected $primaryKey = null;

    protected $foreignKey = 'id_producto';

    protected $relatedKey = 'id_proveedor';

    public $incrementing = false;

    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'id_producto',
        'id_proveedor',
        'precio_compra',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'id_producto' => 'integer',
            'id_proveedor' => 'integer',
            'precio_compra' => 'decimal:2',
        ];
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor', 'id_proveedor');
    }
}
