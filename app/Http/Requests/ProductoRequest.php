<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'nombre_producto' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'stock_actual' => [$this->isMethod('POST') ? 'required' : 'exclude', 'integer', 'min:0', 'max:2147483647'],
            'stock_minimo_alerta' => ['required', 'integer', 'min:0', 'max:2147483647'],
            'precio_venta' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
        ];
    }
}
