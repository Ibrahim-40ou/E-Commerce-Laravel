<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'name'         => 'sometimes|string|max:255',
            'avatar'       => 'sometimes|image|mimes:jpeg,png,jpg,webp|max:2048',
            'email'        => 'sometimes|email|unique:users,email,' . $this->route('user')->id,
            'phone_number' => ['sometimes', 'string', 'regex:/^(964|0)?7[5789]\d{8}$/', 'max:14'],
        ];
    }
}
