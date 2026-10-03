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
        $usuario = $this->input('usuario');

        $this->merge(['es_eventual' => match ($value) {
            'true' => true,
            'false' => false,
            default => $value,
        }]);
        if (is_string($usuario)) {
            $this->merge(['usuario' => strtolower(trim($usuario))]);
        }
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'nombres' => ['required', 'string', 'max:100'],
            'usuario' => [
                $this->isMethod('POST') ? 'required' : 'sometimes', 'string', 'alpha_dash:ascii', 'max:50',
                Rule::unique(Empleado::class, 'usuario')->ignore($this->route('empleado')),
            ],
            'documento_identidad' => [
                'required', 'digits:8',
                Rule::unique(Empleado::class, 'documento_identidad')->ignore($this->route('empleado')),
            ],
            'es_eventual' => ['required', 'boolean'],
        ];
    }
}
