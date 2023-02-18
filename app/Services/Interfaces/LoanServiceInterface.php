<?php

namespace App\Services\Interfaces;

use App\Models\Loan;
use Illuminate\Http\Request;

interface LoanServiceInterface
{
    public function getAllLoansWithDetails(Request $request): array;

    public function createLoan(Request $request): Loan;

    public function updateRepaymentForLoan(Request $request): array;

    public function getLoan(Request $request): array;

    public function createRepayments(Request $request, $loan): void;

    public function updateLoanStatus(Request $request, $status): void;

    public function updateLoanRepayment(Request $request, array $loan): array;

}
