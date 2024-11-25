<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:30|min:3',
            'username' => [
                'nullable',
                'string',
                'max:30',
                'regex:/^[a-z0-9]+$/',
                Rule::unique('users', 'username')->ignore($this->user()->id),
            ],
            'password' => $this->input('password') ? 'required|string' : 'nullable',
            'new_password' => $this->input('password') ? 'required|string|min:8|max:16' : 'nullable',
            'password_confirmation' => $this->input('password') ? 'required|same:new_password' : 'nullable',
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
            'name.string' => 'Nama harus berupa teks!',
            'name.max' => 'Nama maksimal 30 karakter!',
            'name.min' => 'Nama minimal 3 karakter!',
            'username.string' => 'Username harus berupa teks!',
            'username.max' => 'Username maksimal 30 karakter!',
            'username.regex' => 'Username harus berupa huruf kecil dan angka tanpa spasi!',
            'username.unique' => 'Username tersebut sudah ada!',
            'password.required' => 'Password harus diisi!',
            'new_password.required' => 'Password baru harus diisi!',
            'new_password.min' => 'Password baru minimal 8 karakter!',
            'new_password.max' => 'Password baru maksimal 16 karakter!',
            'password_confirmation.required' => 'Konfirmasi password harus diisi!',
            'password_confirmation.same' => 'Konfirmasi password tidak sama dengan password baru!',
        ];
    }
}
