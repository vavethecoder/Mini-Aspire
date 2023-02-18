<?php

return [
    'success' => [
        'REGISTERED' => 'User successfully registered.',
        'LOG_OUT' => 'Successfully logged out.',
        'LOAN_APPLIED' => 'Your loan application received. Loan application number is %s. Subject to approval.',
        'LOAN_APPROVED' => 'Loan application number %s is approved.',
        'REPAYMENT_SUCCESS' => 'Loan repayment is successfully processed.',
    ],
    'error' => [
        'UNAUTHORIZED' => 'Unauthorized.',
        'LOAN_NOT_EXIST' => 'Loan number not exist.',
        'LOAN_APPROVED' => 'Already approved loan.',
        'LOAN_NOT_APPROVED' => 'Loan not yet approved.',
        'LOAN_NOT_ACTIVE' => 'Loan is not active.',
        'LOAN_CLOSED' => 'Already closed loan.',
        'LOAN_OVER_PAYMENT' => 'Payment should not more than balance amount.',
        'LOAN_USER_INVALID' => 'Loan not belongs to user.',
    ]
];
