<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Resources\LoanResource;
use App\Services\LoanService;
use App\Http\Requests\Loan\LoanRequest;
use App\Http\Requests\Loan\LoanApproveRequest;
use App\Models\Loan;
use Auth;

class LoansController extends Controller
{
    public function __construct(protected LoanService $loanService) {}

    public function index()
    {
        $loans = $this->loanService->getAllByUser(Auth::user());

        return LoanResource::collection($loans);
    }

    public function store(LoanRequest $request): LoanResource
    {
        $loan = $this->loanService->store($request->all());

        return (new LoanResource($loan))->additional([
            'message' => 'Loan requested successfully!'
        ]);
    }

    public function approve(LoanApproveRequest $request, Loan $loan): LoanResource
    {
        $loan = $this->loanService->approve($loan);

        return (new LoanResource($loan))->additional([
            'message' => 'Loan request successfully!'
        ]);
    }
}
