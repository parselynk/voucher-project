<?php

namespace App\Repositories;

use App\Repositories\Contracts\VoucherInterface;
use Illuminate\Database\Eloquent\Model;

use App\Offer;
use App\User;
use Carbon\Carbon;

class VoucherRepository implements VoucherInterface
{
    
    protected $model;
    protected $recipient;
    protected $modelInstance = null;

    public function __construct($model, $recipient)
    {
        $this->model = $model;
        $this->recipient = $recipient;
    }

    public function prepareVouchersData($expiry_date)
    {
        $usersArray = $this->availableRecipients()->map(function ($user, $key) use ($expiry_date) {
            return [
                'code' => $this->generateCode(),
                'expire_at' => $this->setDate($expiry_date->endOfDay()),
                'user_id' => $user['id']
            ];
        });

        return $usersArray->toArray();
    }

    public function setDate(Carbon $date)
    {
        return Carbon::parse($date)->endOfDay();
    }

    public function isExpired()
    {
        return $this->modelInstance->where('expire_at', '>=', Carbon::now())->count() > 0 ? false : true ;
    }

    public function isRecipientValid($recipientEmail)
    {
        return $this->modelInstance->whereHas('user', function ($query) use ($recipientEmail) {
            $query->where('email', $recipientEmail);
        })->count() > 0 ? true : false;
    }

    public function verify($voucherCode, $recipientEmail)
    {
        $this->find($voucherCode);
        $this->guardAgainsEmptyInstance();
        
        if ($this->isUsed() || $this->isExpired() || !$this->isRecipientValid($recipientEmail)) {
            return false;
        }
        return $this->setAsUsed() ?  $this->updatedInstance($voucherCode) : false ;
    }

    public function updatedInstance($voucherCode = null)
    {
        return $this->model->where('code', $voucherCode)->first();
    }

    public function setAsUsed()
    {
        return $this->modelInstance->update(['used_at' => Carbon::now()]);
    }

    public function isUsed()
    {
        return $this->modelInstance->first()->used_at !== null ? true : false ;
    }

    public function find(string $voucherCode)
    {
        $this->modelInstance = $this->model->where('code', $voucherCode)->count() > 0 ? $this->model->where('code', $voucherCode) : null ;
        return $this;
    }

    public function availableRecipients()
    {
        return $this->recipient->all();
    }

    public function all()
    {
        return $this->model->all();
    }

    /**
     * [availableVouchersForRecipient find available vouchers for current recipient]
     *
     * @todo query needs to be optimized, for now only it passes the test. other quesries are tested ass well
     *
     * @param  [string] $recipientEmail [email]
     * @return [collection]           [available vouchers for this recipient]
     */
    public function availableVouchersForRecipient($recipientEmail = null)
    {
        $recipient = $this->recipient->where('email', $recipientEmail)->first();
        if ($recipient) {
            return $recipient->vouchers->count() > 0 ?
                $recipient->vouchers->where('used_at', null)
                                    ->where('expire_at', '>=', Carbon::now()) : collect([]) ;
        }

        return collect([]);
    }

    public function usedVouchers()
    {
        return $this->model->whereNotNull('used_at')->get();
    }

    public function unusedVouchers()
    {
        return $this->model->whereNull('used_at')->get();
    }

    public function model()
    {
        return $this->model;
    }

    protected function generateCode()
    {
        return str_random(10);
    }

    protected function guardAgainsEmptyInstance()
    {
        if ($this->modelInstance === null) {
            throw new \Exception("Voucher is invalid", 1);
            return false;
        }

        return true;
    }
}
