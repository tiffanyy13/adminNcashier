<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest {
    public function authorize(): bool { return $this->user()->hasRole('admin'); }
    public function rules(): array {
        return [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:150',
            'package_size' => ['required', 'string', 'max:50',
                Rule::unique('products')->where(fn ($q) => $q->where('category_id', $this->category_id)->where('name', $this->name))
                    ->ignore($this->route('product')?->id)],
            'unit_of_measure' => 'required|string|max:30',
            'unit_price' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'minimum_stock' => 'required|integer|min:0',
        ];
    }
    public function messages(): array { return ['package_size.unique' => 'This product + package size already exists in that category.']; }
}
