<?php

namespace App\Http\Requests;

class UpdateReportRequest extends StoreReportRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'remove_photo_ids' => ['nullable', 'array'],
            'remove_photo_ids.*' => ['integer'],
        ]);
    }
}
