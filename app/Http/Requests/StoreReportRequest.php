<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'category_id' => [
                'required', 'integer',
                Rule::exists('categories', 'id')->where('community', $this->route('community')),
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'location' => ['nullable', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:20000'],
            'photos' => ['nullable', 'array', 'max:10'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.exists' => 'Kategori tidak valid untuk komunitas Anda.',
            'end_date.after_or_equal' => 'Tanggal selesai harus setelah tanggal mulai.',
            'photos.max' => 'Maksimal 10 foto.',
            'photos.*.image' => 'File foto harus berupa gambar.',
            'photos.*.mimes' => 'Format foto harus JPG, PNG, atau WEBP.',
            'photos.*.max' => 'Ukuran tiap foto maksimal 5 MB.',
        ];
    }
}
