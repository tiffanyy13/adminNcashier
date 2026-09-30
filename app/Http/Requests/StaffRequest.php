<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        $id = $this->route('staff')?->id;

        return ['name' => 'required|max:100', 'contact' => 'nullable|max:50',
            'email' => ['required', 'email', Rule::unique('users')->ignore($id)],
            'role' => 'required|in:cashier,secretary',
            'password' => [$id ? 'nullable' : 'required', 'min:8'], 'is_active' => 'nullable|boolean'];
    }
}
