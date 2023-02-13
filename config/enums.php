<?php

return [
    'api_status' => [
        'SUCCESS' => 'Success',
        'ERROR' => 'Error',
        'UNAUTHORIZED' => 'Unauthorized',
    ],
    'token_type' => [
        'BEARER' => 'bearer',
    ],
    'roles' => [
        'ADMIN' => 1,
        'USER' => 2,
    ],
    'loan_status' => [
        'APPROVED' => 1,
        'REJECTED' => 2,
        'PENDING' => 3,
        'CLOSED' => 4,
    ],
    'repayment_status' => [
        'PAID' => 1,
        'PART' => 2,
        'DUE' => 3,
    ],
];
