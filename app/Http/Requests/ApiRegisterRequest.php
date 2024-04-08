<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Response;

class ApiRegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(response([
            'status' => false,
            'errors' => $validator->errors(),
        ], 422));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'min:3|required|max:255',
            'tel' => 'required|unique:users|min:18|max:18',
            'email' => 'required|email|unique:users|max:255'
        ];
    }
    public function messages(): array
    {
        return [
            'name.required' => 'Поле имя обязательное',
            'name.min' => 'Поле имя минимум 3 символа',
            'name.max' => 'Поле имя максимум 255 символов',

            'tel.required' => 'Поле телефон обязательное',
            'tel.unique' => 'Этот номер телефона уже зарегистрирован',
            'tel.min' => 'Поле телефон должно содержать 18 символов',
            'tel.max' => 'Поле телефон должно содержать 18 символов',

            'email.required' => 'Поле Email обязательное',
            'email.email' => 'Email не является валидным',
            'email.unique' => 'Этот Email уже зарегистрирован',
            'email.max' => 'Поле Email максиимум 255 символов'
        ];
    }
}
