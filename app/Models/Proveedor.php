<?php

namespace App\Models;

use Database\Factories\ProveedorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proveedor extends Model
{
    /** @use HasFactory<ProveedorFactory> */
    use HasFactory;

    protected $table = 'proveedor';

    protected $primaryKey = 'id_proveedor';

    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'nombre_empresa',
    ];

    public function productosProveedores(): HasMany
    {
        return $this->hasMany(ProductoProveedor::class, 'id_proveedor', 'id_proveedor');
    }

    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'producto_proveedor', 'id_proveedor', 'id_producto', 'id_proveedor', 'id_producto')
            ->using(ProductoProveedor::class)
            ->withPivot('precio_compra');
    }
}
