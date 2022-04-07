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
        return Loan::find($this->route('loan'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $loan = Loan::find($this->route('loan'));
        return [
            'loan' => function ($attribute, $value, $fail) use ($loan) {
                        if ($loan->status === LoanStatus::APPROVED) {
                            $fail('This loan is already approved');
                        }
                        if ($loan->status !== LoanStatus::PENDING) {
                            $fail('This loan cant be approved');
                        }
                    },
        ];
    }
}
