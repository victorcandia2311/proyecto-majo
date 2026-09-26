<?php

namespace App\Http\Requests;

use App\Models\Empleado;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AsistenciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'id_empleado' => ['required', 'integer', Rule::exists(Empleado::class, 'id_empleado')],
            'fecha' => ['required', 'date_format:Y-m-d'],
            'hora_ingreso' => ['nullable', 'required_with:hora_salida', 'date_format:H:i,H:i:s'],
            'hora_salida' => ['nullable', 'date_format:H:i,H:i:s', 'after_or_equal:hora_ingreso'],
        ];
    }
}
