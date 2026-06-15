<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email|max:254',
            'phone_number' => ['required', 'string', 'unique:users,phone_number','regex:/^(964|0)?7[5789]\d{8}$/', 'max:14'],
            'password'     => 'required|string|confirmed|min:8|max:128'
        ];
    }
}
