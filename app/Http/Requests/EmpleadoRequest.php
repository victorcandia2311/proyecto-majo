<?php

namespace App\Http\Requests;

use App\Models\Empleado;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmpleadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $value = $this->input('es_eventual', false);

        $this->merge(['es_eventual' => match ($value) {
            'true' => true,
            'false' => false,
            default => $value,
        }]);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'nombres' => ['required', 'string', 'max:100'],
            'usuario' => [
                'nullable', 'string', 'alpha_dash:ascii', 'max:50',
                Rule::unique(Empleado::class, 'usuario')->ignore($this->route('empleado')),
            ],
            'documento_identidad' => [
                'required', 'string', 'max:20',
                Rule::unique(Empleado::class, 'documento_identidad')->ignore($this->route('empleado')),
            ],
            'es_eventual' => ['required', 'boolean'],
        ];
    }
}
