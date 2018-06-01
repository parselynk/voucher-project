<?php

namespace App\Repositories\Contracts;

use App\User;

interface VoucherInterface
{
    public function prepareVouchersData($expiry_date);
}
