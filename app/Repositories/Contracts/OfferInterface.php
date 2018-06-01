<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;


use App\User;

interface OfferInterface
{
    public function generateVouchers($expiry_date);
}
