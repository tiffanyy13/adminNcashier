<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest {
    public function authorize(): bool { return $this->user()->hasRole('cashier'); }
    public function rules(): array {
        $m = $this->input('payment_mode');
        $wallet = in_array($m, ['gcash','maya','bank_transfer']);
        return [
            'payment_mode' => ['required', Rule::in(['cash','gcash','maya','bank_transfer','check','pdc'])],
            'amount_received' => [Rule::requiredIf($m === 'cash'), 'nullable', 'numeric', 'min:0'],
            'reference_number' => [Rule::requiredIf($wallet), 'nullable', 'string', 'max:100'],
            'proof' => [Rule::requiredIf($wallet), 'nullable', 'image', 'max:4096'],
            'check_number' => [Rule::requiredIf(in_array($m, ['check','pdc'])), 'nullable', 'string', 'max:50'],
            'bank_name' => [Rule::requiredIf(in_array($m, ['check','pdc'])), 'nullable', 'string', 'max:100'],
            'check_date' => [Rule::requiredIf($m === 'check'), 'nullable', 'date'],
            'maturity_date' => [Rule::requiredIf($m === 'pdc'), 'nullable', 'date', 'after_or_equal:today'],
        ];
    }
    public function messages(): array {
        return ['proof.required' => 'Proof of payment screenshot is required.', 'maturity_date.required' => 'Maturity date is required for post-dated checks.'];
    }
}
