<?php

namespace App\Repositories;

use App\Models\Repayment;
use App\Repositories\Interfaces\RepaymentRepositoryInterface;

class RepaymentRepository implements RepaymentRepositoryInterface
{
    private Repayment $repayment;

    public function __construct(Repayment $repayment)
    {
        $this->repayment = $repayment;
    }

    public function create(array $data): Repayment
    {
        return $this->repayment::create($data);
    }

    public function updateById(int $id, array $data): int
    {
        return $this->repayment::select()->where('id', $id)->update($data);
    }

}
