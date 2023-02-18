<?php

namespace App\Http\Controllers;

use App\Http\Middleware\HasAdminPermission;
use App\Services\Interfaces\LoanServiceInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends Controller
{
    protected $loanService;

    public function __construct(LoanServiceInterface $loanService)
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware(HasAdminPermission::class);

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

    public function approveLoan(Request $request)
    {
        $request->validate([
            'loan_number' => 'required|numeric|gt:0',
        ]);

        $loan = $this->loanService->getLoan($request);

        if (empty($loan)) {
            return response()->json([
                'status' => config('enums.api_status')['ERROR'],
                'message' => config('messages.error')['LOAN_NOT_EXIST'],
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($loan['status'] === config('enums.loan_status')['APPROVED']) {
            return response()->json([
                'status' => config('enums.api_status')['ERROR'],
                'message' => config('messages.error')['LOAN_APPROVED'],
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->loanService->updateLoanStatus($request, config('enums.loan_status')['APPROVED']);
        $this->loanService->createRepayments($request, $loan);

        return response()->json([
            'status' => config('enums.api_status')['SUCCESS'],
            'message' => sprintf(config('messages.success')['LOAN_APPROVED'], $loan['id']),
        ], Response::HTTP_OK);
    }
}
