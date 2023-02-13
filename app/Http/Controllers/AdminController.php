<?php

namespace App\Http\Controllers;

use App\Http\Middleware\HasAdminPermission;
use App\Services\LoanService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware(HasAdminPermission::class);
    }

    public function getAllLoans(Request $request, LoanService $loanService)
    {
        $loans = $loanService->getLoans();

        $repayments = $loanService->getRepaymentsForAllLoans($loans);

        $loanDetails = $loanService->getLoanDetails($loans, $repayments);

        return response()->json([
            'status' => config('enums.api_status')['SUCCESS'],
            'loans' => $loanDetails,
        ],Response::HTTP_OK);
    }

    public function approveLoan(Request $request, LoanService $loanService)
    {
        $request->validate([
            'loan_number' => 'required|numeric|gt:0',
        ]);

        $loan = $loanService->getLoan($request);
        $approvedStatus = config('enums.loan_status')['APPROVED'];

        if($loan === null) {
            return response()->json([
                'status' => config('enums.api_status')['ERROR'],
                'message' => config('messages.error')['LOAN_NOT_EXIST'],
            ], Response::HTTP_BAD_REQUEST);
        }

        if($loan->status === $approvedStatus) {
            return response()->json([
                'status' => config('enums.api_status')['ERROR'],
                'message' => config('messages.error')['LOAN_APPROVED'],
            ], Response::HTTP_BAD_REQUEST);
        }

        $loanService->updateLoanStatus($request, config('enums.loan_status')['APPROVED']);

        $loanService->createRepayments($request);

        return response()->json([
            'status' => config('enums.api_status')['SUCCESS'],
            'message' => sprintf(config('messages.success')['LOAN_APPROVED'], $loan->id),
        ],Response::HTTP_OK);
    }
}
