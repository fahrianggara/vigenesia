<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangePhotoRequest extends FormRequest
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
            'photo' => $this->input('delete') ? 'nullable' : 'required|image|mimes:jpg,jpeg,png|max:10240',
            'delete' => 'sometimes|boolean',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'photo.required' => 'Foto harus diisi!',
            'photo.image' => 'Foto harus berupa gambar!',
            'photo.mimes' => 'Foto harus berformat jpg, jpeg, atau png!',
            'photo.max' => 'Ukuran foto maksimal 10MB!',
        ];
    }
}
