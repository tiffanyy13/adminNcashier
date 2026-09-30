<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest {
    public function authorize(): bool { return $this->user()->hasRole('admin'); }
    public function rules(): array {
        return [
            'customer_name' => 'required|string|max:150', 'customer_contact' => 'nullable|string|max:50',
            'customer_company' => 'nullable|string|max:150', 'delivery_address' => 'nullable|string|max:500',
            'action' => 'required|in:quotation,pending_approval',
            'items' => 'required|array|min:1', 'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1', 'items.*.quoted_price' => 'required|numeric|min:0',
        ];
    }
}
