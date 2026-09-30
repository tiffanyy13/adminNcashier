<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class SupplierRequest extends FormRequest {
    public function authorize(): bool { return $this->user()->hasRole('admin'); }
    public function rules(): array {
        return ['name' => 'required|max:150', 'contact_person' => 'nullable|max:100', 'phone' => 'nullable|max:50',
                'email' => 'nullable|email', 'categories_supplied' => 'nullable|max:255', 'notes' => 'nullable|max:1000'];
    }
}
