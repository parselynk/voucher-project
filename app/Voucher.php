<?php

namespace App;

use App\User;
use App\Offer;
use Carbon\Carbon;


use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    
    /**
     * Fields that can be mass assigned.
     *
     * @var array
     */
    protected $fillable = ['user_id','expire_at','code','used_at'];

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function setAsUsed()
    {
        $this->used_at = Carbon::now();
        $this->save();
    }
}
