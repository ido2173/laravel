<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'tag_id' => ['sometimes', 'integer', 'exists:tags,id'],
            'include' => ['sometimes', 'array'],
            'include.*' => ['string', Rule::in(['body', 'tags'])],
        ];
    }
}
