<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class SaleUpdateRequest extends FormRequest {
    public function authorize(): bool { return $this->user()->hasRole('admin'); }
    public function rules(): array {
        return ['payment_mode' => 'required|in:cash,gcash,maya,bank_transfer,check,pdc', 'amount_paid' => 'required|numeric|min:0',
                'reference_number' => 'nullable|max:100', 'check_maturity_date' => 'nullable|date',
                'reason' => 'required|string|max:255'];
    }
}
