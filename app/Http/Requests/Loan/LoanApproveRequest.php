<?php

namespace App\Http\Requests\Loan;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Loan;
use App\Enums\LoanStatus;

class LoanApproveRequest extends FormRequest
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
        $loan = $this->route('loan');

        return [
            'approve' => [
                'required',
                'boolean',
                function ($attribute, $value, $fail) use ($loan) {
                    if ($loan->status === LoanStatus::APPROVED) {
                        return $fail('This loan is already approved');
                    }
                    if ($loan->status !== LoanStatus::PENDING) {
                        return $fail('This loan cant be approved');
                    }
                },
            ]
        ];
    }
}
