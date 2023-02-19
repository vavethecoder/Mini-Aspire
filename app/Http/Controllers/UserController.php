<?php

namespace App\Http\Controllers;

use App\Http\Middleware\HasUserPermission;
use App\Services\Interfaces\LoanServiceInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    private LoanServiceInterface $loanService;

    public function __construct(LoanServiceInterface $loanService)
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware(HasUserPermission::class);

        $this->loanService = $loanService;
    }

    public function getAllLoans(Request $request)
    {
        $allLoans = $this->loanService->getAllLoansWithDetails($request);

        return response()->json([
            'status' => config('enums.api_status')['SUCCESS'],
            'loans' => $allLoans,
        ], Response::HTTP_OK);
    }

    public function applyLoan(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|gt:0',
            'term' => 'required|integer|gt:0',
        ]);

        $loan = $this->loanService->createLoan($request);

        return response()->json([
            'status' => config('enums.api_status')['SUCCESS'],
            'message' => sprintf(config('messages.success')['LOAN_APPLIED'], $loan->id),
        ], Response::HTTP_CREATED);
    }

    public function repaymentLoan(Request $request)
    {
        $request->validate([
            'loan_number' => 'required|numeric|gt:0',
            'payment' => 'required|numeric|gt:0',
        ]);

        $loan = $this->loanService->updateRepaymentForLoan($request);

        if (isset($loan['error'])) {
            return response()->json([
                'status' => config('enums.api_status')['ERROR'],
                'message' => $loan['error']['message'],
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'status' => config('enums.api_status')['SUCCESS'],
            'loans' => $loan,
        ], Response::HTTP_OK);

    }
}
