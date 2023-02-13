<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\Repayment;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;

class LoanService
{
    public function getLoans($userId = null): Collection|null
    {
        $loans = Loan::all();
        if ($userId !== null) {
            $loans = $loans->where('user', '=', $userId);
        }

        return $loans;
    }

    public function getRepayments($loanId): Collection|null
    {
        return Repayment::all()
            ->where('loan', '=', $loanId)
            ->sortBy('due_date');
    }

    public function getPaidRepayments(): int
    {
        return Repayment::all()->where('status', '=', config('enums.repayment_status')['PAID'])->count();
    }

    public function getRepaymentsForAllLoans(Collection $loans): Collection|null
    {
        $loanIds = [];
        foreach ($loans as $loan) {
            $loanIds[] = $loan->id;
        }

        return Repayment::all()->whereIn('loan', $loanIds);
    }

    public function getLoan(Request $request): Loan|null
    {
        return Loan::whereId($request->loan_number)->first();
    }

    public function getLoanDetails($loans, $repayments): array
    {
        $loanDetails = [];
        $repaymentDeatils = [];

        foreach ($repayments as $i => $repayment) {
            $repaymentDeatils[$repayment->loan][$i]['emi'] = $repayment->emi;
            $repaymentDeatils[$repayment->loan][$i]['due_date'] = $repayment->due_date;
            $repaymentDeatils[$repayment->loan][$i]['status'] = $repayment->status;
        }

        foreach ($loans as $i => $loan) {
            $loanDetails[$i]['loan_number'] = $loan->id;
            $loanDetails[$i]['amount'] = $loan->amount;
            $loanDetails[$i]['term'] = $loan->term;
            $loanDetails[$i]['balance'] = $loan->balance;
            $loanDetails[$i]['status'] = $loan->status;
            if (isset($repaymentDeatils[$loan->id])) {
                $loanDetails[$i]['repayment'] = $repaymentDeatils[$loan->id];
            }
        }
        return $loanDetails;
    }

    public function createLoan(Request $request): Loan
    {
        return Loan::create([
            'user' => (auth('api')->user())->id,
            'amount' => $request->amount,
            'term' => $request->term,
            'balance' => $request->amount,
            'status' => config('enums.loan_status')['PENDING'],
        ]);
    }

    public function createRepayments(Request $request): void
    {
        $loan = $this->getLoan($request);
        $emi = number_format((float)($loan->amount / $loan->term), 2, '.', '');
        $dueDate = (new \DateTime('now'))->setTime(23, 59, 59);

        for ($i = 0; $i < $loan->term; $i++) {
            $dueDate = $dueDate->add(new \DateInterval('P7D'));
            Repayment::create([
                'loan' => $request->loan_number,
                'emi' => $emi,
                'due_date' => $dueDate,
                'status' => config('enums.repayment_status')['DUE'],
            ]);
        }
    }

    public function updateLoanStatus(Request $request, $status): void
    {
        Loan::where('id', $request->loan_number)->update([
            'status' => $status
        ]);
    }

    public function updateLoanRepayment(Request $request, Loan $loan, Collection $repayments): void
    {
        $paid = $loan->balance;
        $payment = $request->payment;
        foreach ($repayments as $repayment) {
            if ($repayment->status === config('enums.repayment_status')['PAID']) {
                $paid -= $repayment->emi;
                continue;
            }

            if ($repayment->status === config('enums.repayment_status')['PART']) {
                $payment += $paid;
            }

            switch (true) {
                case $payment >= $repayment->emi :
                    $payment = number_format((float)($payment - $repayment->emi), 2, '.', '');
                    Repayment::where('id', '=', $repayment->id)->update([
                        'status' => config('enums.repayment_status')['PAID']
                    ]);
                    break;
                case $payment > 0 && $payment < $repayment->emi :
                    $payment = number_format((float)($payment - $repayment->emi), 2, '.', '');
                    Repayment::where('id', '=', $repayment->id)->update([
                        'status' => config('enums.repayment_status')['PART']
                    ]);
                    break;
            }
        }

        $balance = number_format((float)($loan->balance - $request->payment), 2, '.', '');
        if($loan->term === $this->getPaidRepayments()) {
            Loan::where('id', $request->loan_number)->update(['balance' => $balance, 'status' => config('enums.loan_status')['CLOSED']]);
        } else {
            Loan::where('id', $request->loan_number)->update(['balance' => $balance]);
        }

    }

}
