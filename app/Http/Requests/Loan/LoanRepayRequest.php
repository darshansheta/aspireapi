<?php

namespace App\Http\Requests\Loan;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\LoanService;

class LoanRepayRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->route('loan');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $loan        = $this->route('loan');
        $loanService = resolve(LoanService::class);
        return [
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                function ($attribute, $value, $fail) use ($loan, $loanService) {
                    $remainingAmount = $loan->remaining_amount;

                    if (!$remainingAmount) {
                        return $fail('This loan is already paid');
                    }

                    if ($value > $remainingAmount) {
                        return $fail('Maximum amount you can pay is '. $remainingAmount);
                    }

                    if ($loanService->isRepaymentMissed($loan)) {
                        return $fail('You have missed repayment cycle. Please contact us');
                    }
                },
            ]
        ];
    }
}
