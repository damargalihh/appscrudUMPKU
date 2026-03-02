<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SelfRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => 'required|string',
            'email'    => 'nullable|email',
            'password' => 'required|string|min:4',
            'profile'  => 'nullable|string',
        ];
    }
}
