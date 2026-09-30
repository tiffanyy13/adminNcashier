<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class ApproveOrderRequest extends FormRequest {
    public function authorize(): bool { return $this->user()->hasRole('admin'); }
    public function rules(): array {
        return ['discount_type' => 'nullable|in:none,percentage,fixed,special_price', 'discount_value' => 'nullable|numeric|min:0'];
    }
}
