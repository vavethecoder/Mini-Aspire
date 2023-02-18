<?php

namespace App\Repositories\Interfaces;

use App\Models\Loan;
use Illuminate\Support\Collection as SupportCollection;

interface LoanRepositoryInterface
{
    public function findByIdAndUserId(int $loanId, int $userId = null): SupportCollection|null;

    public function findByUserId(int $userId = null): SupportCollection|null;

    public function create(array $data): Loan;

    public function updateById(int $id, array $data): int;

}
