<?php

namespace App\Services;

use App\Models\Loan;
use App\Repositories\Interfaces\LoanRepositoryInterface;
use App\Repositories\Interfaces\RepaymentRepositoryInterface;
use App\Services\Interfaces\LoanServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LoanService implements LoanServiceInterface
{
    private LoanRepositoryInterface $loanRepository;

    private RepaymentRepositoryInterface $repaymentRepository;

    public function __construct(LoanRepositoryInterface $loanRepository, RepaymentRepositoryInterface $repaymentRepository)
    {
        $this->loanRepository = $loanRepository;
        $this->repaymentRepository = $repaymentRepository;
    }

    public function getAllLoansWithDetails(Request $request): array
    {
        $userId = null;
        $user = auth('api')->user();
        if (!is_null($user) && $user->isUser()) {
            $userId = $user->id;
        }

        $loans = $this->loanRepository->findByUserId($userId);

        $loanDetails = [];
        foreach ($loans as $i => $loan) {
            $loanDetails[$loan->id]['loan_number'] = $loan->id;
            $loanDetails[$loan->id]['amount'] = $loan->amount;
            $loanDetails[$loan->id]['term'] = $loan->term;
            $loanDetails[$loan->id]['balance'] = $loan->balance;
            $loanDetails[$loan->id]['status'] = $loan->loan_status;
            if ($loan->emi !== null) {
                $loanDetails[$loan->id]['repayments'][$i]['emi'] = $loan->emi;
                $loanDetails[$loan->id]['repayments'][$i]['due_date'] = $loan->due_date;
                $loanDetails[$loan->id]['repayments'][$i]['status'] = $loan->repayment_status;
            }
        }

        Log::channel('request')->info('Get all loan details of user for Correlation ID : ' . $request->header('X-Correlation-ID'));

        return $loanDetails;
    }

    public function createLoan(Request $request): Loan
    {
        $loan = $this->loanRepository->create($request->all());

        Log::channel('request')->info('Loan applied for Correlation ID : ' . $request->header('X-Correlation-ID'));

        return $loan;
    }

    public function updateRepaymentForLoan(Request $request): array
    {
        $repayments = [];

        $loan = $this->getLoan($request);
        Log::channel('request')->info('Get loan details for Correlation ID : ' . $request->header('X-Correlation-ID'));

        if (!empty($loan) && $loan['status'] != config('enums.loan_status')['APPROVED']) {
            $repayments['error']['message'] = config('messages.error')['LOAN_NOT_ACTIVE'];
            return $repayments;
        }

        if (!empty($loan) && $loan['balance'] < $request->payment) {
            $repayments['error']['message'] = config('messages.error')['LOAN_OVER_PAYMENT'];
            return $repayments;
        }

        Log::channel('request')->info('Loan repayment updated for Correlation ID : ' . $request->header('X-Correlation-ID'));
        return $this->updateLoanRepayment($request, $loan);
    }

    public function getLoan(Request $request): array
    {
        $userId = null;
        $user = auth('api')->user();
        if (!is_null($user) && $user->isUser()) {
            $userId = $user->id;
        }

        $loans = $this->loanRepository->findByIdAndUserId($request->loan_number, $userId);

        $loanDetails = [];
        foreach ($loans as $i => $loan) {
            $loanDetails[$loan->id]['id'] = $loan->id;
            $loanDetails[$loan->id]['amount'] = $loan->amount;
            $loanDetails[$loan->id]['term'] = $loan->term;
            $loanDetails[$loan->id]['balance'] = $loan->balance;
            $loanDetails[$loan->id]['status'] = $loan->loan_status;
            if ($loan->emi !== null) {
                $loanDetails[$loan->id]['repayments'][$i]['id'] = $loan->repayment_id;
                $loanDetails[$loan->id]['repayments'][$i]['emi'] = $loan->emi;
                $loanDetails[$loan->id]['repayments'][$i]['due_date'] = $loan->due_date;
                $loanDetails[$loan->id]['repayments'][$i]['status'] = $loan->repayment_status;
            }
        }

        Log::channel('request')->info('Get loan details of user for Correlation ID : ' . $request->header('X-Correlation-ID'));

        return array_pop($loanDetails);
    }

    public function createRepayments(Request $request, $loan): void
    {
        $emi = number_format((float)($loan['amount'] / $loan['term']), 2, '.', '');
        $dueDate = (new \DateTime('now'))->setTime(23, 59, 59);
        $last_emi = $loan['amount'] - ($emi * ($loan['term'] - 1));

        for ($i = 0; $i < $loan['term']; $i++) {
            if ($loan['term'] - $i === 1) {
                $emi = $last_emi;
            }
            $dueDate = $dueDate->add(new \DateInterval('P7D'));
            $data = [
                'loan' => $request['loan_number'],
                'emi' => $emi,
                'due_date' => $dueDate,
                'status' => config('enums.repayment_status')['DUE'],
            ];
            $this->repaymentRepository->create($data);
        }

        Log::channel('request')->info('Loan repayments created for Correlation ID : ' . $request->header('X-Correlation-ID'));
    }

    public function updateLoanStatus(Request $request, $status): void
    {
        $this->loanRepository->updateById($request->loan_number, [
            'status' => $status
        ]);

        Log::channel('request')->info('Loan status updated for Correlation ID : ' . $request->header('X-Correlation-ID'));
    }

    public function updateLoanRepayment(Request $request, array $loan): array
    {
        $paid = $loan['amount'] - $loan['balance'];
        $payment = $request->payment;
        foreach ($loan['repayments'] as &$repayment) {
            if ($repayment['status'] === config('enums.repayment_status')['PAID']) {
                $paid -= $repayment['emi'];
                continue;
            }

            if ($repayment['status'] === config('enums.repayment_status')['PART']) {
                $payment += $paid;
            }

            switch (true) {
                case $payment >= $repayment['emi'] :
                    $repayment['status'] = config('enums.repayment_status')['PAID'];
                    $payment = number_format((float)($payment - $repayment['emi']), 2, '.', '');
                    $data = ['status' => config('enums.repayment_status')['PAID']];
                    $this->repaymentRepository->updateById($repayment['id'], $data);
                    break;
                case $payment > 0 && $payment < $repayment['emi'] :
                    $repayment['status'] = config('enums.repayment_status')['PART'];
                    $payment = number_format((float)($payment - $repayment['emi']), 2, '.', '');
                    $data = ['status' => config('enums.repayment_status')['PART']];
                    $this->repaymentRepository->updateById($repayment['id'], $data);
                    break;
            }
        }

        $balance = number_format((float)($loan['balance'] - $request->payment), 2, '.', '');

        $loan['balance'] = $balance;
        $data = ['balance' => $balance];
        if (intval($balance) === 0) {
            $loan['status'] = $data['status'] = config('enums.loan_status')['CLOSED'];
            $this->loanRepository->updateById($loan['id'], $data);
        } else {
            $this->loanRepository->updateById($loan['id'], $data);
        }

        return $loan;
    }

}
