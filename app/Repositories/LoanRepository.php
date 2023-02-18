<?php

namespace App\Repositories;

use App\Models\Loan;
use App\Repositories\Interfaces\LoanRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;

class LoanRepository implements LoanRepositoryInterface
{
    private Loan $loan;

    public function __construct(Loan $loan)
    {
        $this->loan = $loan;
    }

    public function findByIdAndUserId(int $id, int $userId = null): SupportCollection|null
    {
        $loan = DB::table('loans')
            ->select('loans.id', 'loans.user', 'loans.amount', 'loans.term', 'loans.balance', 'loans.status as loan_status',
                'repayments.id as repayment_id', 'repayments.emi', 'repayments.due_date', 'repayments.status as repayment_status')
            ->leftJoin('repayments', 'loans.id', '=', 'repayments.loan')
            ->where('loans.id', '=', $id)
            ->orderBy('loans.id')
            ->orderBy('repayments.due_date');

        if ($userId !== null) {
            $loan = $loan->where('loans.user', '=', $userId);
        }

        return $loan->get();
    }

    public function findByUserId(int $userId = null): SupportCollection|null
    {
        $loans = DB::table('loans')
            ->select('loans.id', 'loans.user', 'loans.amount', 'loans.term', 'loans.balance', 'loans.status as loan_status',
                'repayments.emi', 'repayments.due_date', 'repayments.status as repayment_status')
            ->leftJoin('repayments', 'loans.id', '=', 'repayments.loan')
            ->orderBy('loans.id')
            ->orderBy('repayments.due_date');

        if ($userId !== null) {
            $loans = $loans->where('loans.user', '=', $userId);
        }

        return $loans->get();
    }

    public function create(array $data): Loan
    {
        return $this->loan::create([
            'user' => (auth('api')->user())->id,
            'amount' => $data['amount'],
            'term' => $data['term'],
            'balance' => $data['amount'],
            'status' => config('enums.loan_status')['PENDING'],
        ]);
    }

    public function updateById(int $id, array $data): int
    {
        return $this->loan::select()->where('id', $id)->update($data);
    }

}
