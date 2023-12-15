<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255', // Máximo de 255 caracteres
                "regex:/^[\pL\s\-']+$/u" // Apenas letras, espaços, hifens e apóstrofos
            ],
            'email' => 'required|string|email|max:255|unique:users',
            'password' => [
                'required',
                'string',
                'min:8', // mínimo de 8 caracteres
                Password::min(8) // mínimo de 8 caracteres
                ->mixedCase() // combinação de maiúsculas e minúsculas
                ->letters() // deve conter letras
                ->numbers() // deve conter números
                ->symbols(), // deve conter símbolos
                'confirmed',
            ],
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     */
    protected function failedValidation(Validator $validator)
    {
        $response = response()->json([
            'message' => 'Os dados fornecidos são inválidos.',
            'errors' => $validator->errors(),
        ], 422);

        throw new HttpResponseException($response);
    }
}

