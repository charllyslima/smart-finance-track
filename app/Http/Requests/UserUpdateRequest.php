<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserUpdateRequest extends FormRequest
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
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                "regex:/^[\pL\s\-'.]+$/u"
            ],
            'email' => 'required|string|email|max:255|unique:users',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
                'name.required' => 'O campo nome é obrigatório.',
                'name.string' => 'O campo nome deve ser uma string.',
                'name.max' => 'O campo nome deve ter no máximo 255 caracteres.',
                'name.regex' => 'O campo nome contém caracteres inválidos. Permitidos: letras, espaços, hifens, pontos e aspas simples.',

                'email.required' => 'O campo e-mail é obrigatório.',
                'email.string' => 'O campo e-mail deve ser uma string.',
                'email.email' => 'O campo e-mail deve ser um endereço de e-mail válido.',
                'email.max' => 'O campo e-mail deve ter no máximo 255 caracteres.',
                'email.unique' => 'O e-mail já está em uso.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     */
    protected function failedValidation(Validator $validator): void
    {
        $response = response()->json([
            'message' => 'Os dados fornecidos são inválidos.',
            'errors' => $validator->errors(),
        ], 422);

        throw new HttpResponseException($response);
    }
}
