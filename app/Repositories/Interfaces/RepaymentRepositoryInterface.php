<?php

namespace App\Repositories\Interfaces;

use App\Models\Repayment;

interface RepaymentRepositoryInterface
{
    public function create(array $data): Repayment;

    public function updateById(int $id, array $data): int;

}
