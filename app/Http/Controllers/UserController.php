<?php

namespace App\Http\Controllers;

use App\Http\Middleware\HasUserPermission;
use App\Models\Loan;
use App\Services\LoanService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware(HasUserPermission::class);
    }

    public function applyLoan(Request $request, LoanService $loanService)
    {
        $request->validate([
            'amount' => 'required|numeric|gt:0',
            'term' => 'required|integer|gt:0',
        ]);

        $loan = $loanService->createLoan($request);

        return response()->json([
            'status' => config('enums.api_status')['SUCCESS'],
            'message' => sprintf(config('messages.success')['LOAN_APPLIED'], $loan->id),
        ], Response::HTTP_CREATED);
    }

    public function getAllLoans(Request $request, LoanService $loanService)
    {
        $userId = (auth('api')->user())->id;

        $loans = $loanService->getLoans($userId);

        $repayments = $loanService->getRepaymentsForAllLoans($loans);

        $loanDetails = $loanService->getLoanDetails($loans, $repayments);

        return response()->json([
            'status' => config('enums.api_status')['SUCCESS'],
            'loans' => $loanDetails,
        ], Response::HTTP_OK);
    }

    public function repaymentLoan(Request $request, LoanService $loanService)
    {
        $request->validate([
            'loan_number' => 'required|numeric|gt:0',
            'payment' => 'required|numeric|gt:0',
        ]);

        $loan = $loanService->getLoan($request);
        if($loan !== null && (auth('api')->user())->id !== $loan->user) {
            return response()->json([
                'status' => config('enums.api_status')['ERROR'],
                'message' => config('messages.error')['LOAN_USER_INVALID'],
            ], Response::HTTP_BAD_REQUEST);
        }

        if($loan !== null && $loan->status === config('enums.loan_status')['CLOSED']) {
            return response()->json([
                'status' => config('enums.api_status')['ERROR'],
                'message' => config('messages.error')['LOAN_CLOSED'],
            ], Response::HTTP_BAD_REQUEST);
        }

        if($loan !== null && $loan->balance < $request->payment) {
            return response()->json([
                'status' => config('enums.api_status')['ERROR'],
                'message' => sprintf(config('messages.error')['LOAN_OVER_PAYMENT'], $loan->balance),
            ], Response::HTTP_BAD_REQUEST);
        }

        $repayments = $loanService->getRepayments($request->loan_number);

        $loanService->updateLoanRepayment($request, $loan, $repayments);

        return response()->json([
            'status' => config('enums.api_status')['SUCCESS'],
            'message' => config('messages.success')['REPAYMENT_SUCCESS'],
        ], Response::HTTP_OK);

    }
}
