<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|min:3|max:30',
            'username' => 'required|string|unique:users,username|regex:/^[a-z0-9]+$/',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|max:8',
            'password_confirmation' => 'required|same:password',
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
            'name.required' => 'Nama harus diisi!',
            'username.required' => 'Username harus diisi!',
            'username.regex' => 'Username harus berupa huruf kecil dan angka tanpa spasi!',
            'username.unique' => 'Username tersebut sudah ada!',
            'email.required' => 'Email harus diisi!',
            'email.unique' => 'Email tersebut sudah terdaftar!',
            'email.email' => 'Silahkan masukkan email yang valid!',
            'password.required' => 'Password harus diisi!',
            'password.min' => 'Password minimal 6 karakter!',
            'password.max' => 'Password maksimal 8 karakter!',
            'password_confirmation.required' => 'Konfirmasi password harus diisi!',
            'password_confirmation.same' => 'Konfirmasi password tidak sama dengan password!',
        ];
    }
}
