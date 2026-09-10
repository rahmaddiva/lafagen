<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('categories', 'name')
                    ->where('community', $this->route('community'))
                    ->ignore($this->route('category')),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Kategori dengan nama ini sudah ada di komunitas Anda.',
        ];
    }
}
