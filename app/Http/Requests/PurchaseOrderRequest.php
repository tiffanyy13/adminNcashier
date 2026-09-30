<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class PurchaseOrderRequest extends FormRequest {
    public function authorize(): bool { return $this->user()->hasRole('admin'); }
    public function rules(): array {
        return ['supplier_id' => 'required|exists:suppliers,id', 'order_date' => 'required|date',
                'items' => 'required|array|min:1', 'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1', 'items.*.unit_price' => 'required|numeric|min:0'];
    }
}
