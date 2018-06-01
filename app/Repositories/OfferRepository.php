<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use App\Repositories\Contracts\OfferInterface;
use App\Repositories\Contracts\VoucherInterface;

use App\Offer;
use App\User;

class OfferRepository implements OfferInterface
{
    
    protected $model;
    protected $voucher;
    protected $status = null;
    protected $message = null;
    protected $dataset = null;
    protected $instanceObject;

    public function __construct(VoucherInterface $voucher, $model)
    {
        $this->model = $model;
        $this->voucher = $voucher;
    }

    public function all()
    {
        return $this->model->orderBy('id', 'desc')->get();
    }

    public function status()
    {
        return $this->status;
    }
    public function for($offer_id)
    {
        $this->instanceObject = $this->model->find($offer_id);
        return $this;
    }

    public function message()
    {
        return $this->message;
    }

    public function dataset()
    {
        return $this->dataset;
    }

    public function create($request)
    {
        

        try {
            $this->dataset = $this->model->create($request);
            $this->status = true;
            $this->message = "Offer is succesfully created.";
        } catch (\Exception $e) {
            $this->status = false ;
            $this->message = "This Offer already exists." . $e->getMessage();
        }

        return $this;
    }

    public function model()
    {
         return $this->model;
    }

    public function statistics()
    {
         return [
                   'total_vouchers' => $this->voucher->all()->count(),
                   'unused_vouchers' => $this->voucher->unusedVouchers()->count(),
                   'used_vouchers' => $this->voucher->usedVouchers()->count(),
                   'total_recipients' => $this->voucher->availableRecipients()->count(),
                ];
    }

    public function getTotalRecipientsAttribute()
    {
        return $this->voucher->availableRecipients()->count();
    }

    public function voucher()
    {
         return $this->voucher;
    }

    public function generateVouchers($expiry_date)
    {

        try {
            $this->dataset = $this->instanceObject->vouchers()
                                  ->createMany($this->voucher->prepareVouchersData($expiry_date));
            $this->status = true;
            $this->message = "{$this->dataset->count()} vouchers are succesfully made for offer: {$this->instanceObject->name} ";
        } catch (\Exception $e) {
            $this->status = false ;
            $this->message = "Voucher already exits" . $e->getMessage();
        }

        return $this;
    }
}
