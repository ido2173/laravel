<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('changeRole', $this->route('user')) ?? false;
    }

    public function rules(): array
    {
        return [
            'role' => ['required', 'string', Rule::in(['author', 'admin'])],
        ];
    }
}
