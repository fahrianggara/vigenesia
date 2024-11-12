<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostRequest extends FormRequest
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
            'title' => 'required|string|min:3|max:100',
            'content' => 'required|string|min:10',
            'category_id' => 'required|exists:categories,id',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png|max:10240',
            'status' => 'required|in:draft,published',
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
            'title.required' => 'Judul harus diisi!',
            'title.min' => 'Judul minimal 3 karakter!',
            'title.max' => 'Judul maksimal 100 karakter!',
            'content.required' => 'Konten harus diisi!',
            'content.min' => 'Konten minimal 10 karakter!',
            'category_id.required' => 'Kategori harus diisi!',
            'category_id.exists' => 'Kategori tidak ditemukan!',
            'thumbnail.image' => 'Thumbnail harus berupa gambar!',
            'thumbnail.mimes' => 'Thumbnail harus berformat jpg, jpeg, atau png!',
            'thumbnail.max' => 'Ukuran thumbnail maksimal 10MB!',
            'status.required' => 'Status harus diisi!',
            'status.in' => 'Status harus draft atau published!',
        ];
    }
}
