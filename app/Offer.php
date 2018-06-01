<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Voucher;
use Carbon\Carbon;

class Offer extends Model
{
    /**
     * Fields that can be mass assigned.
     *
     * @var array
     */
    protected $fillable = ['name','discount'];

    public function vouchers()
    {
        return $this->hasMany(Voucher::class);
    }

    public function vouchersCount()
    {
        return $this->vouchers()->count();
    }

    public function usedVouchers()
    {
        return $this->vouchers()->whereNotNull('used_at')->count();
    }

    public function unusedVouchers()
    {
        return $this->vouchersCount() - $this->usedVouchers();
    }


    public function setDiscountAttribute($value)
    {
        $this->attributes['discount'] = (float)( $value / 100);
    }
}
