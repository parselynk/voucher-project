<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Repositories\Contracts\OfferInterface;
use App\Repositories\Contracts\VoucherInterface;

class ApiController extends Controller
{
    
    private $offer;
    private $voucher;

    public function __construct(OfferInterface $offer, VoucherInterface $voucher)
    {
        $this->offer = $offer;
        $this->voucher = $voucher;
    }

    /**
     * verify voucher.
     *
     * @return \Illuminate\Http\Response
     */
    public function verify()
    {
        $rules = [ 'email' => 'required|email',
                   'code' => 'required|string|min:10' ];

        $messages = [ 'code.min' => 'Voucher code should not  should not be less than 10 chars',
                      'email.required' => 'email is not provided' ];

        // validate request
        $validate = $this->validateRequest($rules, $messages);
        if (!$validate->passes()) {
            return response()->json([
                'data' => [ 'errors' => $validate->errors() ],
                'success' => false
            ], 404);
        }
        
        // validate voucher
        try {
            if ($voucher = $this->voucher->verify(request()->route('code'), request()->route('email'))) {
                $data = [ 'discount' => $voucher->offer->discount * 100 ."%",
                          'used_at' => $voucher->used_at ];

                return response()->json([
                    'data' => $data,
                    'success' => true
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'data' => [ 'errors' => $e->getMessage() ],
                'success' => false
            ], 404);
        }
       
        //return response if voucher is invalid (expired, used or not realated to recipient)
        return response()->json(
            [
                'data' => 'The voucher/ Email is not valid' ,
                'success' => false
            ],
            200
        );
    }

    /**
     * show users available vouchers.
     *
     * @param  string  $recipientEmail
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $rules = [ 'email' => 'required|email'];
        $messages = ['email.required' => 'email is not provided' ];
        
        $validate = $this->validateRequest($rules, $messages);

        if (!$validate->passes()) {
            return response()->json([
                'data' => [ 'errors' => $validate->errors() ],
                'success' => false
            ], 404);
        }
        
        // validate voucher
        
        try {
            $vouchers = $this->voucher->availableVouchersForRecipient(request()->route('email'));

            if ($vouchers->count() > 0) {
                $data = $vouchers->map(function ($voucher, $index) {
                    return [
                        'code' => $voucher->code,
                        'Offer' => $voucher->offer->name,
                        'discount' => $voucher->offer->discount * 100 .'%',
                        'expiry' => \Carbon\Carbon::parse($voucher->expire_at)->toDateString(),
                    ];
                });
                return response()->json([
                    'data' => $data,
                    'success' => true
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'data' => [ 'errors' => $e->getMessage() ],
                'success' => false
            ], 404);
        }
        
        //return response if voucher is invalid (expired, used or not realated to recipient)
        return response()->json(
            [
                'data' => 'The Recipient Email is not valid' ,
                'success' => false
            ],
            200
        );
    }

    protected function validateRequest($rules, $messages)
    {
        return \Validator::make(
            request()->route()->parameters(),
            $rules,
            $messages
        );
    }
}
