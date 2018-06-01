<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\Contracts\OfferInterface;
use Illuminate\Validation\Rule;
use App\Repositories\Contracts\VoucherInterface;
use Carbon\Carbon;

class VoucherController extends Controller
{
    private $offer;
    private $voucher;

    public function __construct(OfferInterface $offer, VoucherInterface $voucher)
    {
        $this->offer = $offer;
        $this->voucher = $voucher;
    }
    
    public function index()
    {
        $offers = $this->offer->all();
        $statistics = $this->offer->statistics();
        $vouchers = $this->voucher->all();
        return view('voucher', compact('offers', 'statistics', 'vouchers'));
    }

    public function createVouchers()
    {
        request()->validate(
            [
                'offer-id' => 'required',
                'expiry-date' => 'required|date|after:today'
            ],
            $this->validationMessages()
        );
        
        $response = $this->offer->for(request('offer-id'))->generateVouchers(new Carbon(request('expiry-date')));

        if (!$response->status()) {
            return back()->withErrors([
                "message" =>  $response->message()
            ]);
        }
        
        if ($response->message() !== null) {
            session()->flash('message', $response->message());
        }

        return redirect()->back();
    }

    public function validationMessages()
    {
        return [
            'offer-id.required' => 'Please Select an offer.',
            'expiry-date.required' => 'Please provide an exipry date for the offer',
            'expiry-date.after' => 'Expiry date must be atleast one day',
        ];
    }
}
