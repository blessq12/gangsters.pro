<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

final class AddClientAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['nullable', 'string', 'max:32'],
            'title' => ['nullable', 'string', 'max:255'],
            'street' => ['required', 'string', 'max:255'],
            'house' => ['required', 'string', 'max:32'],
            'entrance' => ['nullable', 'string', 'max:32'],
            'apartment' => ['nullable', 'string', 'max:32'],
            'comment' => ['nullable', 'string', 'max:1000'],
            'make_default' => ['sometimes', 'boolean'],
        ];
    }
}
